#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h> 

// Credencials de la xarxa Wi-Fi
const char* ssid = "sercommBB1901";
const char* password = "2Q94PHRSJNJM45HM";

// Adreça IP  on s'executa XAMPP per probes
// const char* serverApiUrl = "http://192.168.0.16/traffic-Int/api/index.php"; 

// Adreça IP en entorn real
const char* serverApiUrl = "https://traffic-int.great-site.net/api/index.php"; 

// Un identificador per a aquest semàfor
const int SEMAFOR_ID = 1;

// Per a la comunicació serial amb l'Arduino Due
// ESP32 RX1 (GPIO16), TX1 (GPIO17)
HardwareSerial SerialDue(1); 

// Prototip de la funció d'enviament de dades
void sendDataToAPI(String dataString, String dataType);

void setup() {
  Serial.begin(115200); // Per veure els missatges al Monitor Sèrie de l'ESP32

  // Inicia la comunicació serial amb l'Arduino Due
  SerialDue.begin(9600, SERIAL_8N1, 16, 17); // RX1=16, TX1=17 de l'ESP32

  Serial.println("Iniciant connexió Wi-Fi...");
  WiFi.begin(ssid, password);
  Serial.print("Intentant connectar-se a la xarxa WiFi ");
  Serial.println(ssid);

  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.print(".");
  }
  Serial.println("\nConnectat al WiFi!");
  Serial.print("Adreça IP de l'ESP32: ");
  Serial.println(WiFi.localIP());
}

void loop() {
  // Comprova si hi ha dades disponibles des de l'Arduino Due
  if (SerialDue.available()) {
    String data = SerialDue.readStringUntil('\n'); // Llegeix la línia completa fins al salt de línia
    data.trim(); // Elimina espais en blanc al principi i al final

    Serial.print("Dada rebuda de l'Arduino Due: ");
    Serial.println(data);

    // Processar les dades i enviar-les segons el seu prefix
    if (data.startsWith("AMBIENT,")) {
      sendDataToAPI(data, "ambiental");
    } else if (data.startsWith("VELOCITAT,")) {
      sendDataToAPI(data, "velocitat");
    } else {
      Serial.println("Format de dada desconegut o sense prefix AMBIENT/VELOCITAT.");
    }
  }

  // Petit delay per evitar saturar el loop si no hi ha dades de l'Arduino Due
  delay(10);
}

void sendDataToAPI(String dataString, String dataType) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi no connectat. No es poden enviar dades.");
    return;
  }

  HTTPClient http;
  http.begin(serverApiUrl);
  http.addHeader("Content-Type", "application/json");

  JsonDocument doc; // Per crear el JSON

  doc["semafor_id"] = SEMAFOR_ID;
  doc["type"] = dataType;

  if (dataType == "ambiental") {
    // Farem una còpia per manipular-la sense afectar la cadena original
    String parseString = dataString; 
    parseString.remove(0, 8); // Eliminar "AMBIENT,"

    float temp = parseString.substring(0, parseString.indexOf(',')).toFloat();
    parseString.remove(0, parseString.indexOf(',') + 1);
    float hum = parseString.substring(0, parseString.indexOf(',')).toFloat();
    parseString.remove(0, parseString.indexOf(',') + 1);
    float pres = parseString.substring(0, parseString.indexOf(',')).toFloat();
    parseString.remove(0, parseString.indexOf(',') + 1);
    int pluja = parseString.toFloat(); // L'últim valor és la pluja

    doc["temp"] = temp;
    doc["hum"] = hum;
    doc["pres"] = pres;
    doc["pluja"] = pluja;

    Serial.print("Preparant JSON ambiental: T="); Serial.print(temp);
    Serial.print(", H="); Serial.print(hum);
    Serial.print(", P="); Serial.print(pres);
    Serial.print(", Pluja="); Serial.println(pluja);

  } else if (dataType == "velocitat") {
    String parseString = dataString; 
    parseString.remove(0, 10); // Eliminar "VELOCITAT,"

    float vel = parseString.toFloat();
    doc["vel"] = vel;

    Serial.print("Preparant JSON velocitat: V="); Serial.println(vel);
  }

  String jsonBuffer;
  serializeJson(doc, jsonBuffer); // Converteix el document JSON a una cadena
  Serial.print("JSON a enviar: "); Serial.println(jsonBuffer);

  int httpResponseCode = http.POST(jsonBuffer);

  if (httpResponseCode > 0) {
    Serial.print("Codi HTTP: ");
    Serial.println(httpResponseCode);
    String response = http.getString();
    Serial.print("Resposta del servidor: ");
    Serial.println(response);
  } else {
    Serial.print("Error en la petició HTTP: ");
    Serial.println(httpResponseCode);
    Serial.println(http.errorToString(httpResponseCode)); // Mostra el missatge d'error de l'HTTP client
  }
  http.end();
}