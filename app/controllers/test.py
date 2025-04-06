from core.Database import Database
connection = Database.get_instance()

if connection.is_connected():
    print("✅ Konekcija sa bazom je uspešna.")
else:
    print("❌ Konekcija nije uspela.")
