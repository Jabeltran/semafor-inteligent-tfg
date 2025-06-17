#include <Arduino.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_AHTX0.h>
#include <Adafruit_BMP280.h>
#include <Wire.h>

// A l'Arduino Due, els pins 16 (TX2) i 17 (RX2) corresponen a Serial2.
#define ESP32_SERIAL Serial2

// Inicialització dels sensors AHT20 i BMP280 que fa servir I2C 
Adafruit_AHTX0 aht;
Adafruit_BMP280 bmp;

// Definició dels pins per a les llums del semàfor
const int LED_COTXE_VERD = 22;
const int LED_COTXE_GROC = 23;
const int LED_COTXE_VERMELL = 24;
const int LED_VIANANT_VERD = 28;
const int LED_VIANANT_VERMELL = 29;

// Definim el pin per al sensor d'ultrasons RCWL-1005
const int TRIGGER_PIN = 32; // Per enviar el pols (Trigger)
const int ECHO_PIN = 33; // Per rebre l'eco (ECHO)

// Definim el pin per al sensor de pluja MH-RD
const int PIN_PLUJA = A0; // Sortida analògica A0

// Definició del pin per al sensor LDR
const int LDR_PIN = A1; //

// Definició del pin PWM per controlar la brillantor dels LEDs
const int PWM_CONTROL_PIN = 2; // 

// Definim un conjunt de nombres simbòlics que defineixen l'estat del semàfor
enum EstatSemafor{
  VERD_COTXES,
  GROC_COTXES,
  VERMELL_COTXE,
};

// Iniciem el semàfor al primer estat
EstatSemafor estatActual = VERD_COTXES;

// Duracions dels estats del semafor en milisegons
const long DURACIO_VERD_COTXE = 6000; // 5 segons
const long DURACIO_GROC_COTXE = 3000; // 3 segons
const long DURACIO_VERMELL_COTXE = 5000; // 5 segons (igual al verd de vianants)
const long DURACIO_VERD_VIANANT = 2000; // 2 segons verd fixe per a vianants
const long DURACIO_PARPELLEIG_VIANANTS = 4000; // 3 segons verd de parpelleig per a vianants

// Variable que emmagatzema el temps de quan va canvià d'estat
unsigned long tempsCanviEstatSemafor = 0;

// Variables per a controlar el temps dins de l'estat VERMELL_COTXE
unsigned long tempsIniciVianants = 0;

// Variables per al sensor d'ultrasons
long duracioMicrosegons;
int distanciaCmActual;
int distanciaCmAnterior = -1; // Valor inicial per indicar que no hi ha lectura anterior
unsigned long tempsUltimaLecturaUltrasons = 0;
const long INTERVAL_LECTURA_ULTRASONS = 100;  // Llegim l'estat del sensor cada 100 ms
float velocitatCmsSenseFiltrar = 0;

// Variables per fer la mitjana mòbil de la velocitat i filtrar lectures
const int NUM_LECTURES_VELOCITAT = 3;   // Nombre de lectures per calcular la mitjana
float lecturesVelocitat[NUM_LECTURES_VELOCITAT];
int indexLecturaVelocitat = 0;
float velocitatCmsFiltrada = 0;


const int UMBRAL_PRESENCIA_VEHICLE_MAX = 60;   // Distància màxima per considerar presència
const int UMBRAL_PRESENCIA_VEHICLE_MIN = 30;    // Distància mínima
const long TEMPS_ESTABILITZACIO_PRESENCIA = 500;  // Temps en ms per confirmar presència
unsigned long tempsPrimerVehicleDetectat = 0;
bool vehiclePresent = false;

const int LLINDAR_VELOCITAT_NORMAL = 50;   // Velocitat per sota de la qual s'amplia l'ambre
const int LLINDAR_VELOCITAT_AMPLIACION_MAXIMA = 20; // Velocitat per sota de la qual l'ampliació és màxima
const long AMPLIFICACIO_MAXIMA_GROC = 3000; // Ampliació màxima en milisegons (3 segons extres)

// Variables per al control de la lectura del sensor de pluja
unsigned long tempsUltimaLecturaPluja = 0;
const long INTERVAL_LECTURA_PLUJA = 4000; //600000 10 minuts en milisegons
bool estaPlovent = false;
const long SEGONS_EXTRA_PLUJA = 2000; // Segons extres per sumar al temps de la llum ambar

const int LLINDAR_INICI_PLUJA = 600;  // Considerem que comenca a ploure
const int LLINDAR_FINAL_PLUJA = 700;  // Per sota d'aquest valor estimem que ja no plou

// Variables per al sensors AHT20 i BMP280
float temperatura = 0.0;
float humitat = 0.0;
float pressio = 0.0;
unsigned long tempsUltimaLecturaAmbiental = 0;
const long INTERVAL_LECTURA_AMBIENTAL = 4000; // 10 minuts en milisegons
const long INTERVAL_ENVIAMENT_AMBIENTAL = 4000; // 10 minuts

// Variable 'Flag' per controlar l'enviament de la velocitat només un cop per cicle GROC_COTXES
bool velocitatEnviadaGroc = false;

// Protòtip de les funcions
void configurarPins();
void controlarSemafor();
void llegirVelocitatUltrasons();
int obtenirDistanciaUltrasons();
float aplicarFiltreVelocitat(float novaVelocitat);
void llegirEstatPluja();
void llegirSensorsAmbientals();
void controlarBrillantorLeds();
void enviarDadesAmbientals();

/**
* Funció de configuració inicial del sistema.
* Es crida una sola vegada en iniciar el microcontrolador.
* S'encarrega de configurar els pins, inicialitzar sensors i comunicacions.
**/
void setup() {
  configurarPins();
  tempsCanviEstatSemafor = millis();  
  tempsIniciVianants = millis();
  pinMode(TRIGGER_PIN, OUTPUT); // Configurem el pin Trigger com a sortida
  pinMode(ECHO_PIN, INPUT); // Configurem el pin Echo com a entrada
  digitalWrite(TRIGGER_PIN, LOW); // Inicialitzem el Trigger a nivell baix

  // Inicialitza la comunicació serial USB per a depuració amb el monitor serial.
  Serial.begin(9600);
  Serial.println("Arduino Due: Iniciat.");

  //Inicialitza el port serial per a la comunicació amb l'ESP32 (pins 16 i 17)
  ESP32_SERIAL.begin(9600);
  Serial.println("ESP32_SERIAL (Serial2) inicialitzat a 9600 bauds.");

  // Inicialitzem l'array de lectures de velocitat a zero
  for (int i = 0; i < NUM_LECTURES_VELOCITAT; i++){
    lecturesVelocitat[i] = 0;
  }

  // Inicialitzem els sensors ambientals 
  Serial.println("Inicialitzant sensors ambientals...");
    Wire.begin(); 
    if (!aht.begin()) {
        Serial.println("No s'ha trobat sensor AHT20!");
    }
    if (!bmp.begin(0x77)) { //adreça I2C 
        Serial.println("No s'ha trobat sensor BMP280!");
    }
  // Configuració del pin PWM que controla la intensitat dels leds
  pinMode(PWM_CONTROL_PIN, OUTPUT);

   // Asegurem que la flag d'enviament de velocitat sigui false a l'inici
  velocitatEnviadaGroc = false;
  
}

/**
 * Cridem totes les funcións que s'aniran repetint
 */

void loop() {
  controlarSemafor();
  llegirVelocitatUltrasons();
  llegirEstatPluja();
  llegirSensorsAmbientals();
  controlarBrillantorLeds();

  unsigned long tempsActual = millis();
  static unsigned long tempsUltimEnviamentAmbiental = 0;

  if (tempsActual - tempsUltimEnviamentAmbiental >= INTERVAL_ENVIAMENT_AMBIENTAL){
    tempsUltimEnviamentAmbiental = tempsActual; // Actualitzem l'últim moment d'enviament
    enviarDadesAmbientals();
  }
  

  delay(10); // Petit delay per evitar problemes
}

// Implementem les funcions

/******************************************
 *  Funció per configura els pins d'Arduino 
 ******************************************/
void configurarPins(){
  pinMode(LED_COTXE_VERD, OUTPUT);
  pinMode(LED_COTXE_GROC, OUTPUT);
  pinMode(LED_COTXE_VERMELL, OUTPUT);
  pinMode(LED_VIANANT_VERD, OUTPUT);
  pinMode(LED_VIANANT_VERMELL, OUTPUT);
}


/*************************************************
 *  Funció per enviar les dades ambientals al EP32
 *************************************************/
void enviarDadesAmbientals(){
  ESP32_SERIAL.print("AMBIENT,");
    ESP32_SERIAL.print(temperatura);
    ESP32_SERIAL.print(",");
    ESP32_SERIAL.print(humitat);
    ESP32_SERIAL.print(",");
    ESP32_SERIAL.print(pressio);
    ESP32_SERIAL.print(",");
    ESP32_SERIAL.println(estaPlovent ? 1 : 0);
    Serial.println("Dades ambientals enviades.");
}


/*****************************************
 *  Funció que conté la lògica del semàfor 
 *****************************************/
void controlarSemafor(){
  unsigned long tempsActual = millis();
  unsigned long duracioGrocActual = DURACIO_GROC_COTXE; // Duració base de la llum groga

  // Definim els estats i canvis d'estats
  switch (estatActual){

  // Verd per als cotxes i vermell per vianants
  case VERD_COTXES:
    digitalWrite(LED_COTXE_VERD, HIGH);
    digitalWrite(LED_COTXE_GROC, LOW);
    digitalWrite(LED_COTXE_VERMELL, LOW);
    digitalWrite(LED_VIANANT_VERD, LOW);
    digitalWrite(LED_VIANANT_VERMELL, HIGH);
    if(tempsActual - tempsCanviEstatSemafor >= DURACIO_VERD_COTXE){
      estatActual = GROC_COTXES;
      tempsCanviEstatSemafor = tempsActual;
      // Quan s'entra a GROC_COTXES, resetejem el flag per permetre enviar la velocitat una vegada
      velocitatEnviadaGroc = false;
    }  
    break;

  // Groc per als cotxes i vermell per vianants
  case GROC_COTXES:
    // Comprovem si hi ha un vehicle present
    if(vehiclePresent){
      if(velocitatCmsFiltrada > 5 && velocitatCmsFiltrada < LLINDAR_VELOCITAT_NORMAL && velocitatCmsFiltrada >= LLINDAR_VELOCITAT_AMPLIACION_MAXIMA){
        // Calculem la proporció d'ampliació inversament proporcional a la velocitat
        float proporcio = 1.0 - (float)(velocitatCmsFiltrada - LLINDAR_VELOCITAT_AMPLIACION_MAXIMA) / (LLINDAR_VELOCITAT_NORMAL - LLINDAR_VELOCITAT_AMPLIACION_MAXIMA);
        duracioGrocActual += (unsigned long)(AMPLIFICACIO_MAXIMA_GROC * proporcio);
       
        Serial.print("Ambre ampliat a: ");
        Serial.print(duracioGrocActual);
        Serial.println(" ms");
      } else if (velocitatCmsFiltrada > 5 && velocitatCmsFiltrada < LLINDAR_VELOCITAT_AMPLIACION_MAXIMA){
        // Si la velocitat és molt baixa, ampliem al màxim el tempps
        duracioGrocActual += (unsigned long)AMPLIFICACIO_MAXIMA_GROC;

        Serial.println("Temps ampliat al màxim");
      }

      // Enviem la velocitat filtrada pel port sèrie
      // ESP32_SERIAL.print("VELOCITAT,");
      // ESP32_SERIAL.println(velocitatCmsFiltrada);
      // Serial.println("Dada de velocitat enviada durant l'ambre.");
      
      // Envia la velocitat a l'ESP32 només UNA vegada si encara no s'ha enviat per cicle de GROC_COTXES
      if (!velocitatEnviadaGroc) {
        if (velocitatCmsFiltrada > 5) { 
            ESP32_SERIAL.print("VELOCITAT,");
            ESP32_SERIAL.println(velocitatCmsFiltrada);
            Serial.print("Dada de velocitat enviada a ESP32 durant l'ambre: ");
            Serial.println(velocitatCmsFiltrada); // Per depuració
          } else {
            Serial.println("No s'envia velocitat (insignificant, <= 10 cm/s)."); // Missatge per depuració si no s'envia
          }
        velocitatEnviadaGroc = true; // Marquem que ja s'ha enviat
      }

    }

    // Afegim temps extra si està plovent
    if (estaPlovent) {
      duracioGrocActual += (unsigned long)SEGONS_EXTRA_PLUJA;
      Serial.print("S'afegeixen ");
      Serial.print(SEGONS_EXTRA_PLUJA / 1000.0);
      Serial.println(" segons extra per pluja a l'ambre.");
    }

    digitalWrite(LED_COTXE_VERD, LOW);
    digitalWrite(LED_COTXE_GROC, HIGH);
    digitalWrite(LED_COTXE_VERMELL, LOW);
    digitalWrite(LED_VIANANT_VERD, LOW);
    digitalWrite(LED_VIANANT_VERMELL, HIGH);
    if(tempsActual - tempsCanviEstatSemafor >= duracioGrocActual){
      estatActual = VERMELL_COTXE;
      tempsCanviEstatSemafor = tempsActual;
      tempsIniciVianants = tempsActual; // Registrem el temps d'entrada a l'estat VERMELL_COTXE
    }  
    break;

  // Vermell per als cotxes i seqûència per vianants
  case VERMELL_COTXE:
    digitalWrite(LED_COTXE_VERD, LOW);
    digitalWrite(LED_COTXE_GROC, LOW);
    digitalWrite(LED_COTXE_VERMELL, HIGH);

    // Seqüència per als vianants dins de l'estat VERMELL_COTXE
    if (tempsActual - tempsIniciVianants < DURACIO_VERD_VIANANT){
      // Verd fixe per vianants
      digitalWrite(LED_VIANANT_VERD, HIGH);
      digitalWrite(LED_VIANANT_VERMELL, LOW);

    } else if (tempsActual - tempsIniciVianants < DURACIO_PARPELLEIG_VIANANTS + DURACIO_VERD_VIANANT){
      // Parpelleig verd per a vianants 
      if ((tempsActual - (tempsIniciVianants + DURACIO_VERD_VIANANT)) % 1000 < 500){
        digitalWrite(LED_VIANANT_VERD, HIGH);
      } else {
        digitalWrite(LED_VIANANT_VERD, LOW);
      }
      digitalWrite(LED_VIANANT_VERMELL, LOW);
    
    } else {
      // Vermell fixe per a vianants al final del cicle
      digitalWrite(LED_VIANANT_VERD, LOW);
      digitalWrite(LED_VIANANT_VERMELL, HIGH);

      // Tornem al l'estat VERD_COTXE després de completar el cicle
      if (tempsActual - tempsCanviEstatSemafor >= DURACIO_VERMELL_COTXE){
        estatActual = VERD_COTXES;
        tempsCanviEstatSemafor = tempsActual;
      }    
    }
    break;
  }
}


/**************************************************************
 *  Funció per a obtenir la distància amb el sensor d'ultrasons   
 **************************************************************/
int obtenirDistanciaUltrasons(){

  // Enviem un pols de 10 microsegons al pin TRIGGER
  digitalWrite(TRIGGER_PIN, HIGH);
  delayMicroseconds(10);
  digitalWrite(TRIGGER_PIN, LOW);

  // Mesurem la durada del pols rebut al pin ECHO
  duracioMicrosegons = pulseIn(ECHO_PIN, HIGH);

  // Calculem la distància en centimetres de forma que la velocitat del so és 
  // aproximadament 343 m/s o 29 microsegons per centímetre per al recorregut d'anada i tornada.
  return (duracioMicrosegons / 29) / 2;
   
}


/*************************************************************** 
 *  Funció per aplicar el filtre de mitjana mòbil a la velocitat
 ***************************************************************/

 float aplicarFiltreVelocitat(float novaVelocitat){
    // Sumem la nova lectura de velocitat a la suma total
    float suma = 0;
    lecturesVelocitat[indexLecturaVelocitat] = novaVelocitat;
    indexLecturaVelocitat = (indexLecturaVelocitat + 1) % NUM_LECTURES_VELOCITAT;

    // Calculem la suma de les lectures actuals
    for (int i = 0; i < NUM_LECTURES_VELOCITAT; i++){
        suma += lecturesVelocitat[i];
    }

    // Retornem la mitjana
    return suma / NUM_LECTURES_VELOCITAT;    
}


/*********************************************************************************** 
*   Funció per llegir la velocitat amb del sensor d'ultrasons de forma no bloquejant 
************************************************************************************/
void llegirVelocitatUltrasons(){

  unsigned long tempsActual = millis();

  if (tempsActual - tempsUltimaLecturaUltrasons >= INTERVAL_LECTURA_ULTRASONS){
    tempsUltimaLecturaUltrasons = tempsActual;

    distanciaCmActual = obtenirDistanciaUltrasons();

    // Depuració dels senaor RCWL1005
    //  Serial.print("Distància Ultrasons: ");
    //  Serial.print(distanciaCmActual);  
    //  Serial.println(" cm");

    // Detectar la presència d'un vehicle
    if (distanciaCmActual > UMBRAL_PRESENCIA_VEHICLE_MIN && distanciaCmActual < UMBRAL_PRESENCIA_VEHICLE_MAX) {
      if (!vehiclePresent) {
        if (tempsPrimerVehicleDetectat == 0) {
          tempsPrimerVehicleDetectat = tempsActual;
        } else if (tempsActual - tempsPrimerVehicleDetectat >= TEMPS_ESTABILITZACIO_PRESENCIA) {
          vehiclePresent = true;
          Serial.println("Vehicle present!");
        }
      }
    } else {
      vehiclePresent = false;
      tempsPrimerVehicleDetectat = 0;
    }

    if (vehiclePresent && distanciaCmAnterior != -1) {      
      //Establim un umbral per a canvi de distàncies per a mitigar falses lectures
      int diferenciaDistancia = abs(distanciaCmActual - distanciaCmAnterior);
        if (diferenciaDistancia > 2){
            // Si hi h alectura anterior, calculem la velocitat en cm/s sense filtrar
            velocitatCmsSenseFiltrar = (float)(abs(distanciaCmActual - distanciaCmAnterior)) / (INTERVAL_LECTURA_ULTRASONS / 1000.0);
            // Apliquem el filtre
            velocitatCmsFiltrada = aplicarFiltreVelocitat(velocitatCmsSenseFiltrar);
        } else {
            velocitatCmsSenseFiltrar = 0; // Considerem la velocitat igual a 0 si el canvi és petit
            velocitatCmsFiltrada = aplicarFiltreVelocitat(0); // Mantenim la velocitat filrtada en zero 
        }
    } else {
        velocitatCmsSenseFiltrar = 0;
        velocitatCmsFiltrada = aplicarFiltreVelocitat(0); // Si no hi ha vehicle o lectura
    }    
    distanciaCmAnterior = distanciaCmActual;    
  }  
}

/*************************************************
 *   Funció per llegir l'estat del sensor de pluja
 *************************************************/
void llegirEstatPluja(){
  unsigned long tempsActual = millis();

  if (tempsActual - tempsUltimaLecturaPluja >= INTERVAL_LECTURA_PLUJA){
    tempsUltimaLecturaPluja = tempsActual;
    int valorPluja = analogRead(PIN_PLUJA);

    // Determinem si plou basant-nos en un llindar    
    if (valorPluja < LLINDAR_INICI_PLUJA){
      estaPlovent = true;
      Serial.println("Esta plovent.");

    /* Per evitar oscil·lacions ràpides entre "plovent" i "no plovent" quan el 
    valor del sensor està prop del llindar, es pot implementar una mica d'histeresi*/
    } else if (valorPluja > LLINDAR_FINAL_PLUJA) {
      estaPlovent = false;
      Serial.println("No està plovent.");
    }    
  }  
}

/*********************************************************
 * Funció per llegir les dades dels sensors AHT20 i BMP280
 *********************************************************/
void llegirSensorsAmbientals() {
  unsigned long tempsActual = millis();

  if (tempsActual - tempsUltimaLecturaAmbiental >= INTERVAL_LECTURA_AMBIENTAL) {
      tempsUltimaLecturaAmbiental = tempsActual;

      sensors_event_t h, t;
      aht.getEvent(&h, &t);

      if (isnan(h.relative_humidity) || isnan(t.temperature)) {
          Serial.println("Error llegint AHT20!");
      } else {
          temperatura = t.temperature;
          humitat = h.relative_humidity;
          Serial.print("Temperatura: ");
          Serial.print(temperatura);
          Serial.println(" *C");
          Serial.print("Humitat: ");
          Serial.print(humitat);
          Serial.println(" %");
      }

      if (!bmp.begin(0x77)) { // Adreça I2C
          Serial.println("No s'ha trobat sensor BMP280!");
      } else {
          pressio = bmp.readPressure() / 100.0F; // Pa a hPa
          Serial.print("Pressió: ");
          Serial.print(pressio);
          Serial.println(" hPa");
      }
  }
}

/*********************************************************
 * Funció per controlar la brillantor dels leds
 *********************************************************/
void controlarBrillantorLeds() {
  int ldrValue = analogRead(LDR_PIN);

  // Mapejar el valor de l'LDR a un rang PWM (0-255)
  int pwmValue = map(ldrValue, 200, 980, 50, 255);

  // Limitem el valor PWM
  pwmValue = constrain(pwmValue, 50, 255);

  analogWrite(PWM_CONTROL_PIN, pwmValue);
}