import xml.etree.ElementTree as ET
# Tao nut goc computers
root = ET.Element("computers")
# Tao computer thu nhat
computer1 = ET.SubElement(root, "computer")
name1 = ET.SubElement(computer1, "name")
name1.text = "Dell Inpiron 2020"
cpu1 = ET.SubElement(computer1, "cpu")
cpu1.text = "Core i5 2.6Ghz"
ram1 = ET.SubElement(computer1, "ram")
ram1.text = "8GB"
# Tao computer thu hai
computer2 = ET.SubElement(root, "computer")
name2 = ET.SubElement(computer2, "name")
name2.text = "Lenovo ThinPack 2022"
cpu2 = ET.SubElement(computer2, "cpu")
cpu2.text = "Core i7 3.6Ghz"
ram2 = ET.SubElement(computer2, "ram")
ram2.text = "16GB"
# Tao cay XML
tree = ET.ElementTree(root)
# Ghi ra file Computer.xml
tree.write("Computer.xml", encoding="utf-8", xml_declaration=True)
print("Da tao file Computer.xml thanh cong!")