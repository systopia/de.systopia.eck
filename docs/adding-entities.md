# adding entities

Bevor Du neue entities hinzufügst, plane eine Struktur für Deine Bedürfnisse. Als Beispiel möchten wir die Büroausstattung, 
die von der Organisation genutzt wird über CiviCRM verwalten. Bevor wir nun also einzelne Gegenstände anlegen, müssen wir 
eine gemeinsame Kategorie erstellen.

In der Administrations-Console findet man unter **Customize Data and Screens** nun den Eintrag **ECK EntityTypes**. Dort 
kann mit dem Button **ADD ENTITY TYPE** eine neue Kategorie erstellt werden. Man trägt unter **Entity Type** den Systemnamen
ein und unter **Label** einen für die Benutzer gut verständlichen Namen (siehe screenshot). Bevor wir hier fortfahren können,
müssen wir einmal abspeichern, damit der neue Typ erstellt wird. Aus der Übersicht kann man im Anschluss den neuen Typ bearbeiten,
um beispielsweise mit **ADD SUBTYPE** neue Unterkategorien anzulegen. Bei uns würde eventuell sowas wie Bildschirme Sinn machen, die 
unterschiedliche Eigenschaften aufweisen. Wenn man den neuen Subtype anlegt, findet man auch schon den Hinweis, wo später 
neue Felder speziell für diesen Subtype angelegt werden können. Wenn man nun unter **Edit Entity Type office equipment** bei **Subtype**
über **Edit** mit der mouse schwebt, bekommen wir unten im Bildschirm mit **subtype=** die ID zu diesem Subtype angezeigt
(Integer). Diese wird später benötigt, um eine neue Entität unter dem passenden Subtype zu erzeugen. merken wir uns diese also.
``
Aus der **ECK Entity Types** Übersicht können wir nun über **List entities** neue entities für unsere Kategorie hinzufügen.
``

Erstellen wir als nächstes **Custom Data** für den neuen Entity Type. Als erstes erstellt man wie bekannt ein neues Set 
**ADD SET OF CUSTOM FIELDS**, für unser Beispiel also sowas wie OfficeEquipmentData. **Used For** den Label-Namen unseres
Typen asuwählen und erstmal allgemein **Any** für das gesamte Equipment. Als Feld etwa sowas wie Anzahl. Und den Ablauf eventuell 
wiederholen, um spezielle Felder für unsere Bildschirme anzulegen, mit sowas wie Grösse, Anschlüsse, Zustand etc.

**Für die nächsten Schritte gibt es bisher noch keine GUI, wir verwenden daher API Explorer v4**

Mit **EckOfficeEquipment - get** können wir unsere Entitäten listen, hätten wir bereits welche erzeugt.
Mit **EckOfficeEquipment - create** können wir nun eine neue Entität erzeugen. dafür müssen wir als **EntityType** in diesem
Beispiel OfficeEquipment eintragen und als **subtype** die entsprechende ID (siehe oben wie man diese findet) (siehe screenshot). **Execute**
als Response sollte nun sowas wie
[{"id": 1,"title": "Dell 1234","subtype": 2 }]
ausgegeben werden. 

Wir finden nun zurück in der GUI unter **ECK Entity Types** office equipment **List entities** unsere erzeugte Entität. wenn man den entsprechenden 
Namen unter **Title** anwählt, sieht man alle Details zu der Entität.