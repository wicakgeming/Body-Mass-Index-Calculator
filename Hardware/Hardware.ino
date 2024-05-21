/*
   -------------------------------------------------------------------------------------
   HX711_ADC
   Arduino library for HX711 24-Bit Analog-to-Digital Converter for Weight Scales
   Olav Kallhovd sept2017
   -------------------------------------------------------------------------------------
*/

/*
   This example file shows how to calibrate the load cell and optionally store the calibration
   value in EEPROM, and also how to change the value manually.
   The result value can then later be included in your project sketch or fetched from EEPROM.

   To implement calibration in your project sketch the simplified procedure is as follow:
       LoadCell.tare();
       //place known mass
       LoadCell.refreshDataSet();
       float newCalibrationValue = LoadCell.getNewCalibration(known_mass);
*/
#include <WiFi.h>
#include <HTTPClient.h>

#include <HX711_ADC.h>
#if defined(ESP8266) || defined(ESP32) || defined(AVR)
#include <EEPROM.h>
#endif

#include <NewPing.h>

#define TRIGGER_PIN 5     // Pin trigger sensor ultrasonik
#define ECHO_PIN 4        // Pin echo sensor ultrasonik
#define MAX_DISTANCE 200  // Jarak maksimum yang akan diukur (dalam cm)

NewPing sonar(TRIGGER_PIN, ECHO_PIN, MAX_DISTANCE);
int ma = 200;

//pins:
const int HX711_dout = 19;  //mcu > HX711 dout pin
const int HX711_sck = 18;   //mcu > HX711 sck pin

//HX711 constructor:
HX711_ADC LoadCell(HX711_dout, HX711_sck);

const int calVal_eepromAdress = 0;
unsigned long t = 0;
double bmi;
double newCalibrationValue;

const char* ssid = "F4";
const char* password = "akujawabaik123";

float i;

void setup() {
  Serial.begin(115200);
  delay(10);
  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi...");
  }
  Serial.println("Connected to WiFi");

  Serial.println();
  Serial.println("Starting...");

  LoadCell.begin();
  //LoadCell.setReverseOutput(); //uncomment to turn a negative output value to positive
  unsigned long stabilizingtime = 2000;  // preciscion right after power-up can be improved by adding a few seconds of stabilizing time
  boolean _tare = true;                  //set this to false if you don't want tare to be performed in the next step
  LoadCell.start(stabilizingtime, _tare);
  if (LoadCell.getTareTimeoutFlag() || LoadCell.getSignalTimeoutFlag()) {
    Serial.println("Timeout, check MCU>HX711 wiring and pin designations");
    while (1)
      ;
  } else {
    LoadCell.setCalFactor(1.0);  // user set calibration value (float), initial value 1.0 may be used for this sketch
    Serial.println("Startup is complete");
  }
  while (!LoadCell.update())
    ;
  calibrate();  //start calibration procedure
}

void loop() {
  static boolean newDataReady = 0;
  const int serialPrintInterval = 0;  //increase value to slow down serial print activity

  // check for new data/start next conversion:
  if (LoadCell.update()) newDataReady = true;

  // get smoothed value from the dataset:
  if (newDataReady) {
    if (millis() > t + serialPrintInterval) {
      i = LoadCell.getData();
      Serial.print("Load_cell output val: ");
      Serial.println(i);
      newDataReady = 0;
      t = millis();
    }
  }

  // receive command from serial terminal
  if (Serial.available() > 0) {
    char inByte = Serial.read();
    if (inByte == 't') LoadCell.tareNoDelay();       //tare
    else if (inByte == 'r') calibrate();             //calibrate
    else if (inByte == 'c') changeSavedCalFactor();  //edit calibration value manually
  }

  // check if last tare operation is complete
  if (LoadCell.getTareStatus() == true) {
    Serial.println("Tare complete");
  }

  delay(500);  // Delay antara pengukuran
  int hei = tinggi();
  calculateBMI(static_cast<double>(hei));  // Convert height to double and pass it to calculateBMI

  String bmi_hasil;

  if (bmi < 1.85) {
    bmi_hasil = "Kurang";
  } else if (bmi < 2.5) {
    bmi_hasil = "normal";
  } else if (bmi < 3.0) {
    bmi_hasil = "kelebihan";
  } else {
    bmi_hasil = "obesitas";
  }

if (i>=5000) {
  String address = "https://*********.com/webapi/api/create.php";
  address += "?tinggi=" + String(hei);
  address += "&berat=" + String(i);
  address += "&hasil=" + String(bmi);
  address += "&hasil_char=" + bmi_hasil;

  // Memulai koneksi HTTP
  HTTPClient http;
  http.begin(address);

  // Mengirim GET request
  int httpCode = http.GET();

  // Mengecek respon server
  if (httpCode > 0) {
    String payload = http.getString();
    Serial.println("Response: " + payload);
  } else {
    Serial.println("HTTP request failed");
  }

  // Menutup koneksi HTTP
  http.end();

  // Menunggu sebelum mengirim request berikutnya
  delay(500);  // interval 5 detik
}
  else {
    Serial.print("ngirimnya nanti")
  }
}

void calibrate() {
  Serial.println("***");
  Serial.println("Start calibration:");
  Serial.println("Place the load cell an a level stable surface.");
  Serial.println("Remove any load applied to the load cell.");
  Serial.println("Send 't' from serial monitor to set the tare offset.");

  boolean _resume = false;
  while (_resume == false) {
    LoadCell.update();
    if (Serial.available() > 0) {
      if (Serial.available() > 0) {
        char inByte = Serial.read();
        if (inByte == 't') LoadCell.tareNoDelay();
      }
    }
    if (LoadCell.getTareStatus() == true) {
      Serial.println("Tare complete");
      _resume = true;
    }
  }

  Serial.println("Now, place your known mass on the loadcell.");
  Serial.println("Then send the weight of this mass (i.e. 100.0) from serial monitor.");

  float known_mass = 0;
  _resume = false;
  while (_resume == false) {
    LoadCell.update();
    if (Serial.available() > 0) {
      known_mass = Serial.parseFloat();
      if (known_mass != 0) {
        Serial.print("Known mass is: ");
        Serial.println(known_mass);
        _resume = true;
      }
    }
  }

  LoadCell.refreshDataSet();                                           //refresh the dataset to be sure that the known mass is measured correct
  float newCalibrationValue = LoadCell.getNewCalibration(known_mass);  //get the new calibration value

  Serial.print("New calibration value has been set to: ");
  Serial.print(newCalibrationValue);
  Serial.println(", use this as calibration value (calFactor) in your project sketch.");
  Serial.print("Save this value to EEPROM adress ");
  Serial.print(calVal_eepromAdress);
  Serial.println("? y/n");

  _resume = false;
  while (_resume == false) {
    if (Serial.available() > 0) {
      char inByte = Serial.read();
      if (inByte == 'y') {
#if defined(ESP8266) || defined(ESP32)
        EEPROM.begin(512);
#endif
        EEPROM.put(calVal_eepromAdress, newCalibrationValue);
#if defined(ESP8266) || defined(ESP32)
        EEPROM.commit();
#endif
        EEPROM.get(calVal_eepromAdress, newCalibrationValue);
        Serial.print("Value ");
        Serial.print(newCalibrationValue);
        Serial.print(" saved to EEPROM address: ");
        Serial.println(calVal_eepromAdress);
        _resume = true;

      } else if (inByte == 'n') {
        Serial.println("Value not saved to EEPROM");
        _resume = true;
      }
    }
  }

  Serial.println("End calibration");
  Serial.println("***");
  Serial.println("To re-calibrate, send 'r' from serial monitor.");
  Serial.println("For manual edit of the calibration value, send 'c' from serial monitor.");
  Serial.println("***");
}

void changeSavedCalFactor() {
  float oldCalibrationValue = LoadCell.getCalFactor();
  boolean _resume = false;
  Serial.println("***");
  Serial.print("Current value is: ");
  Serial.println(oldCalibrationValue);
  Serial.println("Now, send the new value from serial monitor, i.e. 696.0");
  float newCalibrationValue;
  while (_resume == false) {
    if (Serial.available() > 0) {
      newCalibrationValue = Serial.parseFloat();
      if (newCalibrationValue != 0) {
        Serial.print("New calibration value is: ");
        Serial.println(newCalibrationValue);
        LoadCell.setCalFactor(newCalibrationValue);
        _resume = true;
      }
    }
  }
  _resume = false;
  Serial.print("Save this value to EEPROM adress ");
  Serial.print(calVal_eepromAdress);
  Serial.println("? y/n");
  while (_resume == false) {
    if (Serial.available() > 0) {
      char inByte = Serial.read();
      if (inByte == 'y') {
#if defined(ESP8266) || defined(ESP32)
        EEPROM.begin(512);
#endif
        EEPROM.put(calVal_eepromAdress, newCalibrationValue);
#if defined(ESP8266) || defined(ESP32)
        EEPROM.commit();
#endif
        EEPROM.get(calVal_eepromAdress, newCalibrationValue);
        Serial.print("Value ");
        Serial.print(newCalibrationValue);
        Serial.print(" saved to EEPROM address: ");
        Serial.println(calVal_eepromAdress);
        _resume = true;
      } else if (inByte == 'n') {
        Serial.println("Value not saved to EEPROM");
        _resume = true;
      }
    }
  }
  Serial.println("End change calibration value");
  Serial.println("***");
}

int tinggi() {
  unsigned int distance = sonar.ping_cm();  // Mengukur jarak dalam centimeter

  if (distance == 0) {
    Serial.println("Error: Tidak dapat mengukur jarak!");
  } else {
    int height = ma - distance;
    Serial.print("tinggi: ");
    Serial.print(height);
    Serial.println(" cm");
    return height;
  }
}

void calculateBMI(double hei) {
  // Ensure that height is not zero to avoid division by zero
  if (hei != 0) {
    // Calculate BMI using weight (i) and height (hei)
    bmi = i / (hei * hei);

    // Display the calculated BMI
    Serial.print("BMI Anda adalah: ");
    Serial.println(bmi);

    // Determine BMI category
    if (bmi < 1.85) {
      Serial.println("Kategori BMI: Kurang Berat Badan");
    } else if (bmi < 2.5) {
      Serial.println("Kategori BMI: Normal");
    } else if (bmi < 3.0) {
      Serial.println("Kategori BMI: Kelebihan Berat Badan");
    } else {
      Serial.println("Kategori BMI: Obesitas");
    }
  } else {
    Serial.println("Error: Height is zero. Unable to calculate BMI.");
  }
}
