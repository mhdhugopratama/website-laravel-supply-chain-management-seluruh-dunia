import os, re
import glob

views_dir = r'c:\laragon\www\global_supply_chain\resources\views'
pattern = re.compile(r'(<h1[^>]*>)\s*<i\s+class="bi[^"]*"[^>]*></i>\s*', re.IGNORECASE)

for root, _, files in os.walk(views_dir):
    for file in files:
        if file.endswith('.blade.php'):
            filepath = os.path.join(root, file)
            with open(filepath, 'r', encoding='utf-8') as f:
                content = f.read()
            
            new_content, count = pattern.subn(r'\g<1>', content)
            
            if count > 0:
                with open(filepath, 'w', encoding='utf-8') as f:
                    f.write(new_content)
                print(f'Updated {filepath} ({count} replacements)')
