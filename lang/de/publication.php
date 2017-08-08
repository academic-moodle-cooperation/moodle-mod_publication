<?php
// This file is part of mod_publication for Moodle - http://moodle.org/
//
// It is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// It is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'publication', language 'de'
 *
 * @package       mod_publication
 * @author        Philipp Hager
 * @author        Andreas Windbichler
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['modulename'] = 'Studierendenordner';
$string['pluginname'] = 'Studierendenordner';
$string['modulename_help'] = 'Der Studierendenordner umfasst folgende Möglichkeiten:

* Teilnehmer/innen können selbstständig Dokumente hochladen. Diese stehen allen weiteren Kursteilnehmer/innen entweder nach Ihrer Prüfung oder sofort zur Verfügung.
* Es besteht die Möglichkeit eine Aufgabe als Grundlage für den Studierendenordner heranzuziehen, wobei die Trainer/innen entscheiden können welche Dokumente für alle sichtbar sein sollen oder die Entscheidung über die Freigabe an die Teilnehmer/innen selbst weiterleiten.';
$string['modulenameplural'] = 'Studierendenordner';
$string['pluginadministration'] = 'Studierendenordner Administration';
$string['publication:addinstance'] = 'Studierendenordner hinzufügen';
$string['publication:view'] = 'Studierendenordner anzeigen';
$string['publication:upload'] = 'Dateien in den Studierendenordner hochladen';
$string['publication:approve'] = 'Entscheiden ob Dateien für alle Teilnehmer/innen sichtbar sein sollen';
$string['publication:grantextension'] = 'Erweiterung zulassen';
$string['search:activity'] = 'Studierendenordner - Aktivitätsinformation';

$string['name'] = 'Name des Studierendenordners';
$string['obtainstudentapproval'] = 'Einverständnis einholen';
$string['saveapproval'] = 'Einverständnis aktualisieren';
$string['configobtainstudentapproval'] = 'Daten werden erst nach Einverständnis der Teilnehmer/innen für alle sichtbar geschaltet.';
$string['hideidnumberfromstudents'] = 'ID-Number verbergen';
$string['hideidnumberfromstudents_desc'] = 'Spalte ID-Number in den öffentlichen Dateien für Teilnehmer/innen verbergen';
$string['obtainteacherapproval'] = 'sofortige Freigabe';
$string['configobtainteacherapproval'] = 'Dateien von Teilnehmer/innen werden sofort ohne Überprüfung für alle sichtbar geschaltet.';
$string['maxfiles'] = 'Anzahl hochladbarer Dateien';
$string['configmaxfiles'] = 'Voreinstellung für die Anzahl von Dateien, die pro Teilnehmer/in im Studierendenordner erlaubt sind.';
$string['maxbytes'] = 'Maximale Dateigröße';
$string['configmaxbytes'] = 'Voreinstellung für die Dateigröße von Dateien im Studierendenordner.';

$string['reset_userdata'] = 'Alle Daten';

// Strings from the File mod_form.
$string['autoimport'] = 'Automatische Synchronisation mit Aufgabe';
$string['autoimport_help'] = 'Wenn aktiviert, werden neue Abgaben in der Aufgabe automatisch in den Studierendenordner übernommen. Eventuelle Zustimmungen müssen neu eingeholt werden.';
$string['configautoimport'] = 'Wenn Sie bevorzugen, dass Abgaben automatisch in den/die Studierendenordner importiert werden. Dieses Feature kann für jeden Studierendenordner separat an-/ausgeschaltet werden.';
$string['availability'] = 'Zeitraum für Uploadmöglichkeit/Einverständniserklärung';

$string['allowsubmissionsfromdate'] = 'von';
$string['allowsubmissionsfromdateh'] = 'Zeitraum für Uploadmöglichkeit/Einverständniserklärung';
$string['allowsubmissionsfromdateh_help'] = 'Im festgelegten Zeitraum können Teilnehmer/innen je nach Modus entweder Dateien hochladen oder ihr Einverständnis für die Sichtbarkeit ihrer Dateien geben. Solange der Zeitraum geöffnet ist, können Sie ihre hochgeladenen Dateien bearbeiten oder ihr Einverständnis für die Sichtbarkeit wieder entziehen.';
$string['allowsubmissionsfromdatesummary'] = 'Diese Aufgabe akzeptiert Abgaben von <strong>{$a}</strong>';
$string['allowsubmissionsanddescriptionfromdatesummary'] = 'Die Aufgabendetails und das Abgabeformular ist von <strong>{$a}</strong> verfügbar';
$string['alwaysshowdescription'] = 'Beschreibung immer anzeigen';
$string['alwaysshowdescription_help'] = 'Wenn diese Option deaktiviert ist, wird die Aufgabenbeschreibung für Teilnehmer/innen nur während des Abgabezeitraums angezeigt.';

$string['duedate'] = 'bis';
$string['duedate_help'] = 'Zum Abgabeende wird die Aufgabe fällig. Wenn spätere Abgaben erlaubt sind, wird jede nach diesem Datum eingereichte Abgabe als verspätet markiert. Um eine Abgabe nach einem bestimmten Verspätungsdatum zu verhindern kann ein letzter Abgabetermin gesetzt werden.';
$string['duedatevalidation'] = 'Das Abgabeende muss später als der Abgabebeginn sein.';

$string['cutoffdate'] = 'Letzter Abgabetermin';
$string['cutoffdate_help'] = 'Diese Funktion sperrt die Abgabe von Lösungen ab diesem Termin, sofern keine Terminverlängerung gewährt wird.';
$string['cutoffdatevalidation'] = 'Der letzte Abgabetermin muss nach dem Abgabebeginn liegen.';
$string['cutoffdatefromdatevalidation'] = 'Der letzte Abgabetermin muss nach der erstmöglichen Abgabe liegen.';

$string['mode'] = 'Modus';
$string['mode_help'] = 'Treffen Sie hier die Entscheidung, ob die Aktivität als “Upload-Platz” für Teilnehmer/innen dienen soll oder Sie eine Aufgabe als Ursprung der Dateien festgelegen wollen.';
$string['modeupload'] = 'Teilnehmer/innen dürfen Dateien hochladen';
$string['modeimport'] = 'Dateien aus Aufgabe importieren';

$string['courseuploadlimit'] = 'Max. Dateigröße Aktivität';
$string['allowedfiletypes'] = 'Erlaubte Dateitypen (;)';
$string['allowedfiletypes_help'] = 'Die akzeptierten Dateitypen können als strichpunktgetrennte Liste von MIME-Typen angegeben werden, z.B. \'video/mp4; audio/mp3; image/png; image/jpeg\'. Alternativ können Sie auch Dateiendungen inclusive Punkt angeben, z.B. \'.png; .jpg\'. Falls das Feld leer ist, sind alle Dateitypen erlaubt.';
$string['allowedfiletypes_err'] = 'Bitte Eingabe überprüfen! Dateitypen enthalten ungültige Sonder- oder Trennzeichen';
$string['obtainteacherapproval_help'] = 'Diese Option legt fest, ob Dateien sofort für alle ohne Prüfung sichtbar werden: <br><ul><li> Ja - Einträge werden sofort nach dem Speichern angezeigt </li><li> Nein - Einträge werden von Trainer/innen geprüft und freigegeben</li></ul>';
$string['assignment'] = 'Aufgabe';
$string['assignment_help'] = 'Aufgabe für den Dateiimport auswählen. Derzeit können Gruppenabgaben nicht zum Import herangezogen werden.';
$string['obtainstudentapproval_help'] = 'Hier legen Sie fest, ob Sie das Einverständnis der Teilnehmer/innen über Moodle (ja) oder auf eine andere Weise einholen (nein). <br> In Moodle können Sie festlegen, von welchen Teilnehmer/innen das Einverständnis eingeholt wird. Erst nach Einverständnis der Teilnehmer/innen sind die Dateien auch wirklich für alle sichtbar.';
$string['choose'] = 'bitte auswählen ...';
$string['importfrom_err'] = 'Sie müssen eine Aufgabe auswählen von der Sie importieren möchten.';
$string['nonexistentfiletypes'] = 'Die folgenden Dateitypen konnten nicht erkannt werden: {$a}';

$string['groupapprovalmode'] = 'Modus Gruppenzustimmung';
$string['groupapprovalmode_help'] = 'Hier legen Sie fest, ob Sie das Einverständnis aller Gruppenmitglieder oder eines einzelnen Gruppenmitglied einholen.<br />Erst nach Einverständnis aller Gruppenmitglieder oder eines einzelnen Gruppenmitglied sind die Dateien auch wirklich für alle sichtbar.';
$string['groupapprovalmode_all'] = '<strong>ALLE</strong> Mitglieder der Gruppe müssen zustimmen';
$string['groupapprovalmode_single'] = 'mindestens <strong>EIN</strong> Mitglied muss zustimmen';

$string['warning_changefromobtainteacherapproval'] = 'Wenn Sie diese Änderung durchführen werden hochgeladene Dateien sofort für andere Teilnehmer/innen sichtbar. Alle bis jetzt hochgeladenen Dateien werden mit diesen Schritt ebenfalls auf sichtbar gesetzt. Sie haben jedoch das Recht Teilnehmer/innen die Sichtbarkeit aktiv zu entziehen.';
$string['warning_changetoobtainteacherapproval'] = 'Wenn Sie diese Änderung durchführen werden hochgeladene Dateien nicht sofort für andere Teilnehmer/innen sichtbar. Sie müssen dann aktiv Dateien von Teilnehmer/innen sichtbar schalten. Alle bis jetzt hochgeladenen Dateien werden mit diesem Schritt ebenfalls auf nicht sichtbar gesetzt.';

$string['warning_changefromobtainstudentapproval'] = 'Wenn Sie diese Änderung durchführen, können nur Sie bestimmen welche Dateien für alle anderen Teilnehmer/innen sichtbar sind. Das Einverständnis von Teilnehmer/innen wird nicht eingeholt. Alle zum Einverständis gekennzeichneten Dateien werden unabhängig von der Teilnehmer/innen-Zustimmung nach dieser Änderung sofort für alle sichtbar.';
$string['warning_changetoobtainstudentapproval'] = 'Wenn Sie diese Änderung durchführen, wird das Einverständnis der Teilnehmer/innen eingeholt. Nach dieser Änderung wird für alle als sichtbar gekennzeichneten Dateien das Einverständnis der einzelnen Teilnehmer/innen eingeholt - die Dateien sind erst nach gegebenem Einverständnis für alle sichtbar.';

// Strings from the File mod_publication_grantextension_form.php.
$string['extensionduedate'] = 'Erweiterung des Abgabeendes';
$string['extensionnotafterduedate'] = 'Das erweiterte Abgabeende muss nach dem (normalen) Abgabeende liegen.';
$string['extensionnotafterfromdate'] = 'Das erweiterte Abgabeende muss nach Abgabeende liegen.';

// Strings from the File index.php.
$string['nopublicationsincourse'] = 'In diesem Kurs existieren keine Studierendenordner.';

// Strings from the File view.php.
$string['allowsubmissionsfromdate_upload'] = 'Uploadmöglickeit von';
$string['allowsubmissionsfromdate_import'] = 'Einverständniserklärung von';
$string['duedate_upload'] = 'Uploadmöglickeit bis';
$string['duedate_import'] = 'Einverständniserklärung bis';
$string['cutoffdate_upload'] = 'Letzte Uploadmöglichkeit bis';
$string['cutoffdate_import'] = 'Letzte Einverständniserklärung bis';
$string['extensionto'] = 'Erweiterung bis';
$string['filedetails'] = 'Details';
$string['assignment_notfound'] = 'Die Aufgabe von der Dateien importiert wurden, konnte nicht mehr gefunden werden.';
$string['assignment_notset'] = 'Es wurde noch keine Aufgabe ausgewählt.';
$string['updatefiles'] = 'Dateien aktualisieren';
$string['updatefileswarning'] = 'Die Dateien der einzelnen Teilnehmer/innen aus dem Studierendenordner werden mit denen der Aufgabe aktualisiert. Bereits sichtbare Dateien eines Teilnehmers/einer Teilnehmerin werden ebenfalls überschrieben, falls diese in der Aufgabe nicht mehr vorhanden sind bzw. geändert wurden - d.h. das Einverständnis zur Sichtbarkeit der einzelnen Teilnehmer/innen bleibt unverändert.';
$string['myfiles'] = 'Meine Dateien';
$string['mygroupfiles'] = 'Meine Gruppendateien';
$string['add_uploads'] = 'Datei hochladen';
$string['edit_uploads'] = 'Dateien bearbeiten/hochladen';
$string['edit_timeover'] = 'Dateien können nur während des Änderungszeitraumes geändert werden.';
$string['approval_timeover'] = 'Sie können ihre Zustimmung nur während des Änderungszeitraumes ändern.';
$string['noentries'] = 'Keine Einträge';
$string['nofiles'] = 'Keine Dateien vorhanden';
$string['nothing_to_show_users'] = 'nichts anzuzeigen - keine Teilnehmer/innen vorhanden';
$string['nothing_to_show_groups'] = 'nichts anzuzeigen - keine Gruppen vorhanden';
$string['notice'] = 'Hinweis:';
$string['notice_uploadrequireapproval'] = 'Alle Dateien, die Sie hier hochladen, werden erst nach Überprüfung durch die Trainer/innen für alle sichtbar.';
$string['notice_uploadnoapproval'] = 'Alle Dateien, die Sie hier hochladen, werden sofort für alle sichtbar geschaltet. Trainer/innen behalten sich das Recht vor die Sichtbarkeit Ihrer Dateien wieder aufzuheben.';
$string['notice_groupimportrequireallapproval'] = 'Entscheiden Sie hier, ob Sie Ihre Gruppendateien allen zur Verfügung stellen. Alle Gruppenmitglieder müssen Ihre Zustimmung geben bevor die Datei sichtbar ist.';
$string['notice_groupimportrequireoneapproval'] = 'Entscheiden Sie hier, ob Sie Ihre Gruppendateien allen zur Verfügung stellen. Eine Zustimmung eines Gruppenmitglieds reicht bereits aus um die Datei sichtbar zu schalten. Bitte klären Sie bevor Sie Ihre Zustimmung abgeben gruppenintern ab, ob Ihre Datei sichtbar sein soll oder nicht!';
$string['notice_importrequireapproval'] = 'Entscheiden Sie hier, ob sie Ihre Dateien allen zur Verfügung stellen.';
$string['notice_importnoapproval'] = 'Folgende Dateien wurden für alle sichtbar geschaltet.';
$string['teacher_pending'] = 'Bestätigung ausstehend';
$string['teacher_approved'] = 'sichtbar (freigegeben)';
$string['teacher_rejected'] = 'abgelehnt';
$string['approved'] = 'Zugestimmt';
$string['show_details'] = 'Details anzeigen';
$string['student_approve'] = 'zustimmen';
$string['student_approved'] = 'Zugestimmt';
$string['student_pending'] = 'verborgen (nicht freigegeben)';
$string['pending'] = 'Ausstehend';
$string['student_reject'] = 'ablehnen';
$string['student_rejected'] = 'Abgelehnt';
$string['rejected'] = 'Abgelehnt';
$string['visible'] = 'sichtbar';
$string['hidden'] = 'verborgen';

$string['allfiles'] = 'Alle Dateien';
$string['publicfiles'] = 'Öffentliche Dateien';
$string['downloadall'] = 'Alle Dateien als ZIP herunterladen';
$string['optionalsettings'] = 'Optionen';
$string['entiresperpage'] = 'Einträge pro Seite';
$string['nothingtodisplay'] = 'Keine Einträge';
$string['nofilestozip'] = 'Keine Dateien zu zippen';
$string['status'] = 'Status';
$string['studentapproval'] = 'Status'; // Previous 'Studierenden Zustimmung'.
$string['studentapproval_help'] = 'In der Spalte Status wird die Rückmeldung des Teilnehmers/der Teilnehmerin angezeigt:

* ? - Einverständnis noch nicht erfolgt
* ✓ - Einverständnis gegeben
* ✖ - Einverständnis entzogen';
$string['teacherapproval'] = 'Zustimmung';
$string['visibility'] = 'für alle sichtbar';
$string['visibleforstudents'] = 'Für alle sichtbar';
$string['visibleforstudents_yes'] = 'Teilnehmer/innen können diese Datei sehen';
$string['visibleforstudents_no'] = 'Diese Datei ist für Teilnehmer/innen NICHT sichtbar';
$string['resetstudentapproval'] = 'Status zurücksetzen'; // Previous 'Studierenden zustimmung zurücksetzen'.

$string['go'] = 'Start';
$string['withselected'] = 'Mit Auswahl...';
$string['zipusers'] = "als ZIP herunterladen";
$string['approveusers'] = "für alle sichtbar";
$string['rejectusers'] = "für alle unsichtbar";
$string['grantextension'] = 'Erweiterung zulassen';
$string['saveteacherapproval'] = 'Zustimmung aktualisieren';
$string['reset'] = 'Zurücksetzen';
$string['savestudentapprovalwarning'] = 'Sind Sie sicher dass Sie diese Änderungen speichern möchten? Der Status kann im Nachhinein nicht mehr geändert werden.';

// Strings from the File  upload.php.
$string['filesofthesetypes'] = 'Dateien dieses Typs können hinzugefügt werden:';
$string['guideline'] = 'sichtbar für alle:';
$string['published_immediately'] = 'ja sofort, ohne Prüfung durch die Trainer/innen';
$string['published_aftercheck'] = 'nein, erst nach Prüfung durch die Trainer/innen';
$string['save_changes'] = 'Änderungen speichern';

// Strings for JS...
$string['total'] = 'Gesamt';
$string['details'] = 'Details';

// Deprecated since Moodle 2.9!
$string['requiremodintro'] = 'Beschreibung notwendig';
$string['configrequiremodintro'] = 'Deaktivieren Sie diese Option, wenn die Eingabe von Beschreibungen für jede Aktivität nicht verpflichtend sein soll.';
