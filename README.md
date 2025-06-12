# Semàfor Intel·ligent per Millorar la Seguretat del trànsit

**Adaptació Dinàmica a Trànsit i Condicions Ambientals**

### Descripció General

Aquest projecte desenvolupa un prototip de semàfor intel·ligent capaç d'adaptar la seva lògica de funcionament a les condicions de trànsit i ambientals en temps real, mitjançant la integració de 
sensors i la comunicació IoT, per millorar la seguretat viària i l'eficiència.

### Motivació

La congestió de trànsit i la ineficiència dels semàfors estàtics són problemes creixents a les ciutats. A més, la durada fixa de la llum groga pot generar indecisió i estrès als conductors, 
augmentant el risc d'accidents. Aquest sistema busca mitigar aquests problemes adaptant-se a les condicions reals de la via.

### Objectius del Projecte

* Detecció de vehicles i càlcul de velocitat.
* Regulació dinàmica de la llum groga del semàfor.
* Integració de sensors ambientals (temperatura, humitat, pressió, pluja, llum).
* Gestió i enviament de dades al núvol (IoT).
* Visualització de dades en un portal web.

### Arquitectura del Sistema

El sistema es compon de: un node de semàfor intel·ligent (Arduino Due + ESP32 + Sensors), una plataforma de comunicació i backend al núvol (API PHP + MySQL) i una aplicació web per a la visualització de dades.

### Components Principals

#### Maquinari:

* **Microcontroladors:** Arduino Due, ESP32
* **Sensors:** LDR (llum), RCWL-1005 (detecció de moviment/velocitat), AHT20 (temperatura/humitat), BMP280 (pressió), MH-RD (pluja).
* **Actuadors:** LEDs de senyalització del semàfor (vermell, groc, verd).
* **Interconnexió:** Protoboard, cables, resistències.

#### Programari:

* **Firmware:** C/C++ per Arduino IDE / PlatformIO.
* **Backend:** PHP, MySQL.
* **Frontend:** HTML, CSS (Bootstrap), JavaScript (jQuery, Chart.js).
* **Llibreries Clau:** `ArduinoJson.h`, `HTTPClient.h`, `Adafruit_Sensor.h`, `Adafruit_AHTX0.h`, `Adafruit_BMP280.h`.

### Funcionalitats Clau

* Adaptació dinàmica de la durada de la llum groga segons la velocitat dels vehicles detectats.
* Ampliació del temps de la llum groga en condicions de pluja per augmentar la seguretat.
* Ajust automàtic de la brillantor dels LEDs del semàfor segons la llum ambiental.
* Monitorització en temps real de paràmetres ambientals (temperatura, humitat, pressió, pluja).
* Recollida i emmagatzematge de dades de trànsit i ambientals a una base de dades al núvol.
* Visualització de dades en temps real i històriques mitjançant un dashboard web interactiu.

### Guia d'Instal·lació i Ús

Per replicar o executar aquest projecte, seguiu els passos bàsics:

1.  **Clonar el Repositori:**
    `git clone https://github.com/Jabeltran/semafor-inteligent-tfg.git`
    `cd semafor-inteligent-tfg`

2.  **Configuració del Firmware (Arduino Due i ESP32):**
    * Obriu els projectes (`.cpp`) amb Arduino IDE o PlatformIO.
    * Instal·leu les llibreries necessàries (`ArduinoJson`, `HTTPClient`, `Adafruit_Sensor`, `Adafruit_AHTX0`, `Adafruit_BMP280`).
    * Configureu les credencials Wi-Fi a l'ESP32.
    * Carregueu el firmware corresponent a cada placa.

3.  **Configuració del Backend (Servidor Web i Base de Dades):**
    * Instal·leu XAMPP al vostre sistema local o configureu el vostre hosting en línia.
    * Importeu l'esquema de la base de dades (`.sql` proporcionat) a MySQL mitjançant phpMyAdmin o la línia de comandes.
    * Col·loqueu els fitxers PHP del backend al directori del servidor web.
    * Assegureu-vos que la configuració de la base de dades als fitxers PHP sigui correcta.

4.  **Accés al Frontend (Aplicació Web):**
    * Un cop el backend estigui configurat, podeu accedir al dashboard web a través del vostre navegador.
    * **Accés en Línia:** `https://traffic-int.great-site.net/login`

### Enllaços Útils

* **Portal Web del Projecte:** <https://traffic-int.great-site.net/login>

### Autor

José Antonio Beltrán

### Llicència

Aquesta obra està subjecta a una llicència de Reconeixement-NoComercial-CompartirIgual 3.0 Espanya de Creative Commons```
