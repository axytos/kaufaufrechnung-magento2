---
author: axytos GmbH
title: "Installationsanleitung"
subtitle: "axytos Kauf auf Rechnung für Magento2"
header-right: axytos Kauf auf Rechnung für Magento2
lang: "de"
titlepage: true
titlepage-rule-height: 2
toc-own-page: true
linkcolor: blue
---

# Installationsanleitung

Das Plugin stellt die Bezahlmethode __Kauf Auf Rechnung__ für den Einkauf in Ihrem Magento Shop bereit.

Einkäufe mit dieser Bezahlmethode werden von axytos ggf. bis zum Forderungsmanagement übernommen.

Alle relevanten Änderungen an Bestellungen mit dieser Bezahlmethode werden automatisch an axytos übermittelt.

Anpassungen über die Installation hinaus, z.B. von Rechnungs- und E-Mail-Templates, sind nicht notwendig.

Weitere Informationen erhalten Sie unter [https://www.axytos.com/](https://www.axytos.com/).


## Voraussetzungen

1. Vertragsbeziehung mit [https://www.axytos.com/](https://www.axytos.com/).

2. Verbindungsdaten, um das Plugin mit [https://portal.axytos.com/](https://portal.axytos.com/) zu verbinden.

Um dieses Plugin nutzen zu können benötigen Sie zunächst eine Vertragsbeziehung mit [https://www.axytos.com/](https://www.axytos.com/).

Während des Onboarding erhalten Sie die notwendigen Verbindungsdaten, um das Plugin mit [https://portal.axytos.com/](https://portal.axytos.com/) zu verbinden.


## Plugin-Installation

### Via Marketplace

1. Plugin im Magento Marketplace nach ["axytos Kauf auf Rechnung"](https://marketplace.magento.com/catalogsearch/result/?q=axytos%20Kauf%20auf%20Rechnung) kostenlos kaufen und hinzufügen.

2. Folgen Sie der Anleitung im Marketplace.

### Via Composer

Installieren Sie [Composer](https://getcomposer.org/).

Führen Sie die folgenden Kommandos in Ihrer Konsole im Root-Verzeichnis Ihrer Magento-Distribution aus.

```
composer require axytos/kaufaufrechnung-magento2
php bin/magento module:enable Axytos_KaufAufRechnung
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:clean
```

Das Plugin ist jetzt installiert und kann konfiguriert und aktiviert werden.

Um das Plugin nutzen zu können, benötigen Sie valide Verbindungsdaten zu [https://portal.axytos.com/](https://portal.axytos.com/) (siehe Voraussetzungen).


## Bezahlmethoden-Konfiguration in Magento

1. Zur Administration Ihrer Magento Distribution wechseln. Die Bezahlmethode axytos Kauf auf Rechnung befindet sich unter STORES > Configuration > SALES > Payment Methods > OTHER PAYMENT METHODS.

2. __Enabled__ auf "Yes" umstellen.

3. __API Host__ auswählen, entweder 'Live' oder 'Sandbox'.

4. __API Key__ eintragen. Der korrekte Wert wird Ihnen während des Onboarding von axytos mitgeteilt (siehe Voraussetzungen).

5. __Client Secret__ eintragen. Der korrekte Wert wird Ihnen ebenfalls im Onboarding mitgeteilt (siehe Voraussetzungen).

6. __Save Config__ ausführen.

Zur Konfiguration müssen Sie valide Verbindungsdaten zu [https://portal.axytos.com/](https://portal.axytos.com/) (siehe Voraussetzungen), d.h. __API Host__, __API Key__ und __Client Secret__ für die Bezahlmethode speichern.

## Kauf auf Rechnung kann nicht für Einkäufe ausgewählt werden?

Überprüfen Sie folgende Punkte:

1. Das Plugin __axytos Kauf auf Rechnung__ ist installiert.

2. Die Bezahlmethode __axytos Kauf auf Rechnung__ ist aktiviert.

3. Die Bezahlmethode __axytos Kauf auf Rechnung__ ist mit korrekten Verbindungsdaten (__API Host__ & __API Key__) konfiguriert.

Fehlerhafte Verbindungsdaten führen dazu, dass das Plugin nicht für Einkäufe ausgewählt werden kann.

