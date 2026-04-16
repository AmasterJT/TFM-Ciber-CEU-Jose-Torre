
import os

OLD_NAME = "pyme-victima"
NEW_NAME = "maquina-victima-ubuntu-TFM"

def renombrar_archivos_vm():
    """Renombra todos los archivos que contengan OLD_NAME por NEW_NAME"""
    
    # Contar antes
    total_archivos = sum(1 for name in os.listdir('.') if OLD_NAME in name)
    print(f"📊 Encontrados {total_archivos} archivos para renombrar:")
    
    # Lista para mostrar antes
    archivos_a_renombrar = []
    for name in os.listdir('.'):
        if OLD_NAME in name:
            archivos_a_renombrar.append(name)
            print(f"   → {name}")
    
    print(f"\n🔄 Cambiando '{OLD_NAME}' → '{NEW_NAME}'")
    print("-" * 60)
    
    renombrados = 0
    errores = 0
    
    for old_name in archivos_a_renombrar:
        old_path = old_name
        new_name = old_name.replace(OLD_NAME, NEW_NAME)
        new_path = new_name
        
        try:
            # Verificar que no existe ya el destino
            if os.path.exists(new_path):
                print(f"⚠️  [SALTA] {new_path} ya existe")
                continue
            
            os.rename(old_path, new_path)
            print(f"✅ [RENOMBRADO] {old_name} → {new_name}")
            renombrados += 1
            
        except Exception as e:
            print(f"❌ [ERROR] {old_name} → {e}")
            errores += 1
    
    print("-" * 60)
    print(f"🎉 PROCESO COMPLETADO")
    print(f"📈 Renombrados: {renombrados}")
    print(f"⚠️  Errores: {errores}")
    print(f"📁 Archivos originales ignorados: {total_archivos - len(archivos_a_renombrar)}")

if __name__ == "__main__":
    print("🚀 RENOMBRADOR VM - VMware/VirtualBox")
    print("=" * 60)
    
    # Verificar que estamos en directorio con archivos VM
    if not any(OLD_NAME in name for name in os.listdir('.')):
        print(f"❌ No se encontraron archivos con '{OLD_NAME}' en el directorio actual")
        print("💡 Ejecuta este script DESDE la carpeta de la VM")
        exit(1)
    
    renombrar_archivos_vm()