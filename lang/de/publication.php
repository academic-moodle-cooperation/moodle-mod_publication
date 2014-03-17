<?php
// This plugin is for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'mod_publication', language 'de'
 * 
 * @package mod_publication
 * @author Andreas Windbichler
 * @copyright TSC
 */

$string['modulename'] = 'Studierendenordner';
$string['pluginname'] = 'Studierendenordner';
$string['modulename_help'] = 'Der Studierendenordner umfasst folgende Möglichkeiten:

* Teilnehmer/innen können selbstständig Dokumente hochladen. Diese stehen allen weiteren Kursteilnehmer/innen entweder nach Ihrer Prüfung oder sofort zur Verfügung.
* Es besteht die Möglichkeit eine Aufgabe als Grundlage für den Studierendenordner heranzuziehen, wobei Sie entscheiden können welche Dokumente für alle sichtbar sein sollen oder die Entscheidung über die Freigabe an die Teilnehmer/innen selbst weiterleiten.';
$string['modulenameplural'] = 'Studierendenordner';
$string['pluginadministration'] = 'Studierendenordner Administration';
$string['publication:addinstance'] = 'Studierendenordner hinzufügen';
$string['publication:view'] = 'Studierendenordner anzeigen';
$string['publication:upload'] = 'Dateien in den Studierendenordner hochladen';
$string['publication:approve'] = 'Entscheiden ob Dateien für alle Studenten sichtbar sein sollen';
$string['publication:grantextension'] = 'Erweiterung zulassen';

$string['name'] = 'Name des Studierendenordners';
$string['requiremodintro'] = 'Beschreibung notwendig';
$string['configrequiremodintro'] = 'Deaktivieren Sie diese Option, wenn die Eingabe von Beschreibungen für jede Aktivität nicht verpflichtend sein soll.';
$string['obtainstudentapproval'] = 'Einverständnis einholen';
$string['saveapproval'] = 'Einverständnis aktualisieren';
$string['configobtainstudentapproval'] = 'Daten werden erst nach Einverstädnis des Studierenden für alle sichtbar geschaltet.';
$string['hideidnumberfromstudents'] = 'ID-Number verbergen';
$string['hideidnumberfromstudents_desc'] = 'Spalte ID-Number in den Öffentlichen Dateien für Studierende verbergen';
$string['obtainteacherapproval'] = 'sofortige Freigabe';
$string['configobtainteacherapproval'] = 'Dateien von Studierenden werden sofort ohne Überprüfung für alle sichtbar geschaltet.';
$string['maxfiles'] = 'Anzahl hochladbarer Dateien';
$string['configmaxfiles'] = 'Voreinstellung für die Anzahl von Dateien, die pro User im Studierendenordner erlaubt sind.';
$string['maxbytes'] = 'Maximale Dateigröße';
$string['configmaxbytes'] = 'Voreinstellung für die Dateigröße von Dateien im Studierendenordner.';

// mod_form
$string['availability'] = 'Zeitraum für Uploadmöglichkeit/Einverständniserklärung';

$string['allowsubmissionsfromdate'] = 'von';
$string['allowsubmissionsfromdate_help'] = 'Wenn diese Option aktiviert ist, können Lösungen nicht vor diesem Zeitpunkt abgegeben werden. Wenn diese Option deaktiviert ist, ist die Abgabe sofort möglich.';
$string['allowsubmissionsfromdatesummary'] = 'This assignment will accept submissions from <strong>{$a}</strong>';
$string['allowsubmissionsanddescriptionfromdatesummary'] = 'The assignment details and submission form will be available from <strong>{$a}</strong>';
$string['alwaysshowdescription'] = 'Beschreibung immer anzeigen';
$string['alwaysshowdescription_help'] = 'Wenn diese Option deaktiviert ist, wird die Aufgabenbeschreibung für Teilnehmer/innen nur während des Abgabezeitraums angezeigt.';

$string['duedate'] = 'bis';
$string['duedate_help'] = 'Zum Abgabetermin wird die Aufgabe fällig. Wenn spätere Abgaben erlaubt sind, wird jede nach diesem Datum eingereichte Abgabe als verspätet markiert. Um eine Abgabe nach einem bestimmten Verspätungsdatum zu verhindern kann ein endgültiges Abgabedatum gesetzt werden.';
$string['duedatevalidation'] = 'Der Abgabetermin muss später als der Abgabebeginn sein.';

$string['cutoffdate'] = 'Letzter Abgabetermin';
$string['cutoffdate_help'] = 'Diese Funktion sperrt die Abgabe von Lösungen ab diesem Termin, sofern keine Terminverlängerung gewährt wird.';
$string['cutoffdatevalidation'] = 'Der letzte Abgabetermin muss nach dem Abgabestart liegen.';
$string['cutoffdatefromdatevalidation'] = 'Der letzte Abgabetermin muss nach der erstmöglichen Abgabe liegen.';

$string['mode'] = 'Modus';
$string['mode_help'] = 'Treffen Sie hier die Entscheidung, ob die Aktivität als “Upload-Platz” für Studierende dienen soll oder Sie eine Aufgabe als Ursprung der Dateien festgelegen wollen.';
$string['modeupload'] = 'Studierende dürfen Dateien hochladen';
$string['modeimport'] = 'Dateien aus Aufgabe importieren';

$string['courseuploadlimit'] = 'Max. Dateigröße Aktivität';
$string['allowedfiletypes'] = 'Erlaubte Dateiendungen (,)';
$string['allowedfiletypes_help'] = 'Hier können Sie die erlaubten Dateiendungen beim Hochladen von Aufgaben setzen, separiert durch Kommas (,). z.B.: txt, jpg.
Wenn jeder Dateityp erlaubt ist, das Feld freilassen. Groß- und Kleinschreibung wird hierbei ignoriert.';
$string['allowedfiletypes_err'] = 'Bitte Eingabe überprüfen! Dateiendungen enthalten ungültige Sonder- oder Trennzeichen';
$string['obtainteacherapproval_help'] = 'Diese Option legt fest, ob Dateien sofort ohne Prüfung sichtbar werden:

* Ja - Einträge werden sofort nach dem Speichern für alle angezeigt
* Nein - Einträge werden von Trainer/innen geprüft und freigegeben';
$string['assignment'] = 'Aufgabe';
$string['obtainstudentapproval_help'] = 'Hier legen Sie fest ob Studierende selbst entscheiden können ob ihre Aufgaben für andere sichtbar sind oder nicht.
Sie können festlegen von welchen Studierenden das Einverständnis eingeholt wird. Erst nach Einverständnis des Studierenden
sind die Dateien auch wirklich sichtbar.';
$string['choose'] = 'bitte auswählen ...';
$string['importfrom_err'] = 'Sie müssen eine Aufgabe auswählen von der Sie importieren möchten.';

$string['warning_changefromobtainteacherapproval'] = 'Wenn Sie diese Änderung durchführen werden hochgeladene Dateien sofort für andere TeilnehmerInnen sichtbar. Alle bis jetzt hochgeladenen Dateien werden mit diesen Schritt ebenfalls auf sichtbar gesetzt. Sie haben jedoch das Recht Studierenden die Sichtbarkeit aktiv zu entziehen.';
$string['warning_changetoobtainteacherapproval'] = 'Wenn Sie diese Änderung durchführen werden hochgeladene Dateien nicht sofort für andere TeilnehmerInnen sichtbar. Sie müssen dann aktiv Dateien von Studierenden sichtbar schalten. Alle bis jetzt hochgeladenen Dateien werden mit diesem Schritt ebenfalls auf nicht sichtbar gesetzt.';

// mod_publication_grantextension_form.php
$string['extensionduedate'] = 'Erweiterung des Abgabdatums';
$string['extensionnotafterduedate'] = 'Das erweiterte Abgabedatum muss nach dem (normalen) Abgabedatum liegen.';
$string['extensionnotafterfromdate'] = 'Das erweiterte Abgabedatum muss nach Abgabedatum liegen.';

// index.php
$string['nopublicationsincourse'] = 'In diesem Kurs existieren keine Studierendenordner.';

// view.php
$string['allowsubmissionsfromdate_upload'] = 'Uploadmöglickeit von';
$string['allowsubmissionsfromdate_import'] = 'Einverständniserklärung von';
$string['duedate_upload'] = 'Uploadmöglickeit bis';
$string['duedate_import'] = 'Einverständniserklärung bis';
$string['cutoffdate_upload'] = 'Letzte Uploadmöglichkeit bis';
$string['cutoffdate_upload'] = 'Letzte Einverständniserklärung bis';
$string['extensionto'] = 'Erweiterung bis';
$string['assignment_notfound'] = 'Die Aufgabe von der Import wird konnte nicht mehr gefunden werden.';
$string['updatefiles'] = 'Dateien aktualisieren';
$string['updatefileswarning'] = 'Die Dateien der einzelnen Studierenden aus dem Studierendenordner werden mit denen der Aufgabe aktualisiert. Bereits sichtbare Dateien eines Studierenden werden ebenfalls überschrieben, falls diese in der Aufgabe nicht mehr vorhanden sind bzw. geändert wurden - d.h. das Einverständnis zur Sichtbarkeit des einzeln Studierenden bleibt unverändert.';
$string['myfiles'] = 'Meine Dateien';
$string['add_uploads'] = 'Datei hochladen';
$string['edit_uploads'] = 'Dateien bearbeiten';
$string['edit_timeover'] = 'Dateien können nur während des Änderungszeitraumes geändert werden.';
$string['approval_timeover'] = 'Sie können ihre Zustimmung nur während des Änderungszeitraumes ändern.';
$string['nofiles'] = 'Keine Dateien vorhanden';
$string['notice'] = 'Hinweis:';
$string['notice_uploadrequireapproval'] = 'Alle Dateien die Sie hier hochladen, werden erst nach Überprüfung durch den Lehrenden für andere Teilnehmer/innen sichtbar.';
$string['notice_uploadnoapproval'] = 'Alle Dateien die Sie hier hochladen, werden sofort für andere Teilnehmer/innen sichtbar geschaltet. Der Lehrende behält sich das Recht vor die Sichtbarkeit Ihrer Dateien wieder aufzuheben.';
$string['notice_importrequireapproval'] = 'Entscheiden Sie hier, ob sie Ihre Dateien allen Teilnehmer/innen zur Verfügung stellen.';
$string['notice_importnoapproval'] = 'Folgende Dateien wurden durch den Lehrenden für alle Teilnehmer/innen sichtbar geschaltet.';
$string['teacher_pending'] = 'Bestätigung ausstehend';
$string['teacher_approved'] = 'freigegeben';
$string['teacher_rejected'] = 'abgelehnt';
$string['student_approve'] = 'zustimmen';
$string['student_approved'] = 'Zugestimmt';
$string['student_pending'] = 'noch keine Angabe';
$string['student_reject'] = 'ablehnen';
$string['student_rejected'] = 'Abgelehnt';
$string['visible'] = 'sichtbar';
$string['hidden'] = 'verborgen';

$string['allfiles'] = 'Alle Dateien';
$string['publicfiles'] = 'Öffentliche Dateien';
$string['downloadall'] = 'Alle Dateien als ZIP herunterladen';
$string['optionalsettings'] = 'Optionen';
$string['entiresperpage'] = 'Einträge pro Seite';
$string['nothingtodisplay'] = 'Keine Einträge';
$string['nofilestozip'] = 'Keine Dateien zu Zipen';
$string['status'] = 'Status';
$string['visibility'] = 'für alle sichtbar';
$string['visibleforstudents'] = 'für Studierende sichtbar';
$string['visibleforstudents_yes'] = 'Studierende können diese Datei sehen';
$string['visibleforstudents_no'] = 'Diese Datei ist für Studierende NICHT sichtbar';
$string['resetstudentapproval'] = 'Status zurücksetzen';

$string['go'] = 'Start';
$string['withselected'] = 'Mit Auswahl...';
$string['zipusers'] = "als ZIP herunterladen";
$string['approveusers'] = "für alle sichtbar";
$string['rejectusers'] = "für alle unsichtbar";
$string['grantextension'] = 'Erweiterung zulassen';
$string['savevisibility'] = 'Sichtbarkeit aktualisieren';
$string['reset'] = 'Zurücksetzen';

// upload.php
$string['guideline'] = 'sichtbar für alle:';
$string['published_emediately'] = 'ja sofort, ohne Prüfung durch den Lehrenden';
$string['published_aftercheck'] = 'nein, erst nach Prüfung durch den Lehrenden';
$string['save_changes'] = 'Änderungen Speichern';