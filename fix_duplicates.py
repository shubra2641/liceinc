import re
import os

def fix_duplicate_keys(file_path):
    """Fix duplicate keys in PHP language file"""
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()

    lines = content.split('\n')
    seen_keys = set()
    fixed_lines = []
    duplicates_removed = 0

    for line in lines:
        # Match PHP array key-value pairs
        match = re.match(r"^\s*'([^']+)'\s*=>\s*'([^']*)',?\s*$", line)
        if match:
            key = match.group(1)
            if key in seen_keys:
                duplicates_removed += 1
                continue  # Skip duplicate
            else:
                seen_keys.add(key)
                fixed_lines.append(line)
        else:
            fixed_lines.append(line)

    # Write back the fixed content
    with open(file_path, 'w', encoding='utf-8') as f:
        f.write('\n'.join(fixed_lines))

    print(f"Removed {duplicates_removed} duplicate keys from {file_path}")

if __name__ == "__main__":
    file_path = r"d:\xampp\htdocs\my-logos\resources\lang\en\app.php"
    fix_duplicate_keys(file_path)