import os, re

views_dir = r'c:\laragon\www\global_supply_chain\resources\views'
# match <div ... class="...nb-card-header..." ...> and then the <i> icon
pattern = re.compile(r'(<div[^>]*class="[^"]*nb-card-header[^"]*"[^>]*>\s*)<i\s+class="bi[^"]*"[^>]*></i>\s*', re.IGNORECASE)

for root, _, files in os.walk(views_dir):
    for file in files:
        if file.endswith('.blade.php'):
            filepath = os.path.join(root, file)
            with open(filepath, 'r', encoding='utf-8') as f:
                content = f.read()
            
            new_content, count = pattern.subn(r'\g<1>', content)
            
            # also, we should check if there are <h1> or <h2> headers that they might refer to?
            # "di semua menu yang ada emoji pada bagian atas bordernya"
            # It really sounds like card headers that I missed. Let's just fix card headers first.
            
            if count > 0:
                with open(filepath, 'w', encoding='utf-8') as f:
                    f.write(new_content)
                print(f'Updated {filepath} ({count} replacements)')
