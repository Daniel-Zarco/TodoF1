import os
import requests
from dotenv import load_dotenv

load_dotenv(dotenv_path=".env")

SUPABASE_URL = os.getenv("SUPABASE_URL")
SUPABASE_KEY = os.getenv("SUPABASE_SERVICE_ROLE_KEY")
BUCKET = os.getenv("SUPABASE_BUCKET", "todo-f1-assets")

drivers = [
    {"driver_slug": "max-verstappen", "team_slug": "red-bull-racing", "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/M/MAXVER01_Max_Verstappen/maxver01.png"},
    {"driver_slug": "sergio-perez", "team_slug": "red-bull-racing", "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/S/SERPER01_Sergio_Perez/serper01.png"},

    {"driver_slug": "charles-leclerc", "team_slug": "ferrari", "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/C/CHALEC01_Charles_Leclerc/chalec01.png"},
    {"driver_slug": "carlos-sainz", "team_slug": "ferrari", "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/C/CARSAI01_Carlos_Sainz/carsai01.png"},

    {"driver_slug": "lewis-hamilton", "team_slug": "mercedes", "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/L/LEWHAM01_Lewis_Hamilton/lewham01.png"},
    {"driver_slug": "george-russell", "team_slug": "mercedes", "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/G/GEORUS01_George_Russell/georus01.png"},

    {"driver_slug": "lando-norris", "team_slug": "mclaren", "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/L/LANNOR01_Lando_Norris/lannor01.png"},
    {"driver_slug": "oscar-piastri", "team_slug": "mclaren", "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/O/OSCPIA01_Oscar_Piastri/oscpia01.png"},

    {"driver_slug": "fernando-alonso", "team_slug": "aston-martin", "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/F/FERALO01_Fernando_Alonso/feralo01.png"},
    {"driver_slug": "lance-stroll", "team_slug": "aston-martin", "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/L/LANSTR01_Lance_Stroll/lanstr01.png"},

    {"driver_slug": "kevin-magnussen", "team_slug": "haas", "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/K/KEVMAG01_Kevin_Magnussen/kevmag01.png"},
    {"driver_slug": "nico-hulkenberg", "team_slug": "haas", "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/N/NICHUL01_Nico_Hulkenberg/nichul01.png"},

    {"driver_slug": "alexander-albon", "team_slug": "williams", "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/A/ALEALB01_Alexander_Albon/alealb01.png"},
    {"driver_slug": "logan-sargeant", "team_slug": "williams", "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/L/LOGSAR01_Logan_Sargeant/logsar01.png"},

    {"driver_slug": "esteban-ocon", "team_slug": "alpine", "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/E/ESTOCO01_Esteban_Ocon/estoco01.png"},
    {"driver_slug": "pierre-gasly", "team_slug": "alpine", "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/P/PIEGAS01_Pierre_Gasly/piegas01.png"},

    {"driver_slug": "yuki-tsunoda", "team_slug": "rb", "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/Y/YUKTSU01_Yuki_Tsunoda/yuktsu01.png"},
    {"driver_slug": "daniel-ricciardo", "team_slug": "rb", "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/D/DANRIC01_Daniel_Ricciardo/danric01.png"},

    {"driver_slug": "valtteri-bottas", "team_slug": "kick-sauber", "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/V/VALBOT01_Valtteri_Bottas/valbot01.png"},
    {"driver_slug": "zhou-guanyu", "team_slug": "kick-sauber", "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/Z/ZHOGUA01_Zhou_Guanyu/zhogua01.png"},
]

headers = {
    "apikey": SUPABASE_KEY,
    "Authorization": f"Bearer {SUPABASE_KEY}",
    "Content-Type": "image/png",
    "x-upsert": "true",
}

for driver in drivers:
    driver_slug = driver["driver_slug"]
    team_slug = driver["team_slug"]
    image_url = driver["url"]

    storage_path = f"drivers/portraits/{driver_slug}/{team_slug}.png"

    print(f"Descargando {driver_slug}...")

    response = requests.get(image_url, timeout=20)

    if response.status_code != 200:
        print(f"ERROR descargando {driver_slug}: {response.status_code}")
        continue

    upload_url = f"{SUPABASE_URL}/storage/v1/object/{BUCKET}/{storage_path}"

    print(f"Subiendo a Supabase: {storage_path}")

    res = requests.post(upload_url, headers=headers, data=response.content)

    if res.status_code in [200, 201]:
        print(f"OK: {driver_slug}")
    else:
        print(f"ERROR subiendo {driver_slug}: {res.status_code} - {res.text}")

print("Proceso terminado.")