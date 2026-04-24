import os
import requests
from dotenv import load_dotenv
from supabase import create_client

load_dotenv()

SUPABASE_URL = os.getenv("SUPABASE_URL")
SUPABASE_KEY = os.getenv("SUPABASE_SERVICE_ROLE_KEY")
BUCKET = os.getenv("SUPABASE_BUCKET", "todo-f1-assets")

supabase = create_client(SUPABASE_URL, SUPABASE_KEY)

drivers = [
    {
        "slug": "max-verstappen",
        "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/M/MAXVER01_Max_Verstappen/maxver01.png",
    },
    {
        "slug": "sergio-perez",
        "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/S/SERPER01_Sergio_Perez/serper01.png",
    },
    {
        "slug": "charles-leclerc",
        "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/C/CHALEC01_Charles_Leclerc/chalec01.png",
    },
    {
        "slug": "carlos-sainz",
        "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/C/CARSAI01_Carlos_Sainz/carsai01.png",
    },
    {
        "slug": "lewis-hamilton",
        "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/L/LEWHAM01_Lewis_Hamilton/lewham01.png",
    },
    {
        "slug": "george-russell",
        "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/G/GEORUS01_George_Russell/georus01.png",
    },
    {
        "slug": "lando-norris",
        "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/L/LANNOR01_Lando_Norris/lannor01.png",
    },
    {
        "slug": "oscar-piastri",
        "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/O/OSCPIA01_Oscar_Piastri/oscpia01.png",
    },
    {
        "slug": "fernando-alonso",
        "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/F/FERALO01_Fernando_Alonso/feralo01.png",
    },
    {
        "slug": "lance-stroll",
        "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/L/LANSTR01_Lance_Stroll/lanstr01.png",
    },
    {
        "slug": "kevin-magnussen",
        "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/K/KEVMAG01_Kevin_Magnussen/kevmag01.png",
    },
    {
        "slug": "nico-hulkenberg",
        "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/N/NICHUL01_Nico_Hulkenberg/nichul01.png",
    },
    {
        "slug": "alexander-albon",
        "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/A/ALEALB01_Alexander_Albon/alealb01.png",
    },
    {
        "slug": "logan-sargeant",
        "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/L/LOGSAR01_Logan_Sargeant/logsar01.png",
    },
    {
        "slug": "esteban-ocon",
        "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/E/ESTOCO01_Esteban_Ocon/estoco01.png",
    },
    {
        "slug": "pierre-gasly",
        "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/P/PIEGAS01_Pierre_Gasly/piegas01.png",
    },
    {
        "slug": "yuki-tsunoda",
        "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/Y/YUKTSU01_Yuki_Tsunoda/yuktsu01.png",
    },
    {
        "slug": "daniel-ricciardo",
        "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/D/DANRIC01_Daniel_Ricciardo/danric01.png",
    },
    {
        "slug": "valtteri-bottas",
        "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/V/VALBOT01_Valtteri_Bottas/valbot01.png",
    },
    {
        "slug": "zhou-guanyu",
        "url": "https://media.formula1.com/d_driver_fallback_image.png/content/dam/fom-website/drivers/Z/ZHOGUA01_Zhou_Guanyu/zhogua01.png",
    },
]

for driver in drivers:
    slug = driver["slug"]
    image_url = driver["url"]

    storage_path = f"drivers/portraits/2024/{slug}.png"

    print(f"Descargando {slug}...")

    response = requests.get(image_url, timeout=20)

    if response.status_code != 200:
        print(f"ERROR descargando {slug}: {response.status_code}")
        continue

    image_bytes = response.content

    print(f"Subiendo a Supabase: {storage_path}")

    try:
        supabase.storage.from_(BUCKET).upload(
            path=storage_path,
            file=image_bytes,
            file_options={
                "content-type": "image/png",
                "upsert": "true",
            },
        )

        print(f"OK: {slug}")

    except Exception as e:
        print(f"ERROR subiendo {slug}: {e}")

print("Proceso terminado.")