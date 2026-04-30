
import os

filepath = r'c:\jokiproyek\magang-rpa\resources\views\hrd\departments\index.blade.php'
with open(filepath, 'r', encoding='utf-8') as f:
    lines = f.readlines()

# Line numbers are 1-indexed in the view, so indices are line_num - 1
# Keep lines 1-134 (indices 0..133)
# Skip lines 135-216 (indices 134..215)
# Keep lines 217-end (indices 216..end)

new_lines = lines[0:134]
# Add closing tags
new_lines.append('                </table>\n')
new_lines.append('            </div>\n')
new_lines.append('        </div>\n')
new_lines.append('    </div>\n')
new_lines.extend(lines[216:])

with open(filepath, 'w', encoding='utf-8') as f:
    f.writelines(new_lines)

print("Successfully fixed index.blade.php")
