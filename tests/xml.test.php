<?php
$reader = new XMLReader();
$reader->open('test.xml');

while ($reader->read()) {
  if ($reader->nodeType == XMLReader::END_ELEMENT) {
    continue;
  }

  if($reader->name == '_title' || $reader->name == '_startDate' || $reader->name == '_endDate') {
    print $reader->readOuterXML();
  }
}
$reader->close();
?>
