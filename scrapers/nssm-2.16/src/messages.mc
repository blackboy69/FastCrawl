LanguageNames =
(
English=0x0409:MSG00409
French=0x40C:MSG0040C
Italian=0x410:MSG00410
)

MessageId = 501
SymbolicName = NSSM_MESSAGE_USAGE
Severity = Informational
Language = English
NSSM: The non-sucking service manager
Version %s, %s
Usage: nssm <option> [args]

To show service installation GUI:

        nssm install [<servicename>]

To install a service without confirmation:

        nssm install <servicename> <app> [<args>]

To show service removal GUI:

        nssm remove [<servicename>]

To remove a service without confirmation:

        nssm remove <servicename> confirm
.
Language = French
NSSM: Le gestionnaire de services Windows pour les professionnels!
Version %s, %s
Syntaxe: nssm <option> [arguments]

Pour afficher l'�cran d'installation du service:

        nssm install [<nom_du_service>]

Pour installer un service sans confirmation:

        nssm install <nom_du_service> <application> [<arguments>]

Pour afficher l'�cran de d�sinstallation du service:

        nssm remove [<nom_du_service>]

Pour d�sinstaller un service sans confirmation:

        nssm remove <nom_du_service> confirm
.
Language = Italian
NSSM: il Service Manager professionale.
Versione %s, %s
Uso: nssm <opzioni> [argomenti]

Per aprire l'interfaccia di INSTALLAZIONE Servizio:

        nssm install [<nomeservizio>]

Per INSTALLARE il servizio da riga di comando:

        nssm install <nomeservizio> <applicazione> [<argomenti>]

Per aprire l'interfaccia di RIMOZIONE Servizio:

        nssm remove [<nomeservizio>]

Per RIMUOVERE il servizio da riga di comando:

        nssm remove <nomeservizio> confirm
.

MessageId = +1
SymbolicName = NSSM_MESSAGE_NOT_ADMINISTRATOR_CANNOT_INSTALL
Severity = Informational
Language = English
Administrator access is needed to install a service.
.
Language = French
Les droits d'administrateur sont requis pour installer un service.
.
Language = Italian
L'installazione di un servizio richiede privilegi di amministratore.
.

MessageId = +1
SymbolicName = NSSM_MESSAGE_NOT_ADMINISTRATOR_CANNOT_REMOVE
Severity = Informational
Language = English
Administrator access is needed to remove a service.
.
Language = French
Les droits d'administrateur sont requis pour d�sinstaller un service.
.
Language = Italian
La rimozione di un servizio richiede privilegi di amministratore.
.

MessageId = +1
SymbolicName = NSSM_MESSAGE_PRE_REMOVE_SERVICE
Severity = Informational
Language = English
To remove a service without confirmation: nssm remove <servicename> confirm
.
Language = French
Pour d�sinstaller un service sans confirmation: nssm remove <nom_du_service> confirm
.
Language = Italian
Per rimuovere un servizio da riga di comando: nssm remove <servicename> confirm
.

MessageId = +1
SymbolicName = NSSM_MESSAGE_OPEN_SERVICE_MANAGER_FAILED
Severity = Informational
Language = English
Error opening service manager!
.
Language = French
Erreur � l'ouverture du gestionnaire de services!
.
Language = Italian
Errore apertura Service Manager!
.

MessageId = +1
SymbolicName = NSSM_MESSAGE_PATH_TOO_LONG
Severity = Informational
Language = English
The full path to %s is too long!
.
Language = French
Le chemin complet vers %s est trop long!
.
Language = Italian
Il path completo verso %s � troppo lungo!
.


MessageId = +1
SymbolicName = NSSM_MESSAGE_OUT_OF_MEMORY_FOR_IMAGEPATH
Severity = Informational
Language = English
Out of memory for ImagePath!
.
Language = French
M�moire insuffisante pour sp�cifier le chemin de l'image (ImagePath)!
.
Language = Italian
Memoria insufficiente per ImagePath!
.

MessageId = +1
SymbolicName = NSSM_MESSAGE_CREATESERVICE_FAILED
Severity = Informational
Language = English
Error creating service!
.
Language = French
Erreur � la cr�ation du service!
.
Language = Italian
Errore creazione servizio!
.

MessageId = +1
SymbolicName = NSSM_MESSAGE_CREATE_PARAMETERS_FAILED
Severity = Informational
Language = English
Error setting startup parameters for the service!
.
Language = French
Erreur en essayant de r�gler les param�tres de d�marrage du service!
.
Language = Italian
Errore durante l'impostazione dei parametri per il servizio!
.

MessageId = +1
SymbolicName = NSSM_MESSAGE_SERVICE_INSTALLED
Severity = Informational
Language = English
Service "%s" installed successfully!
.
Language = French
Le service "%s" a �t� install� avec succ�s!
.
Language = Italian
Servizio "%s" installato correttamente!
.

MessageId = +1
SymbolicName = NSSM_MESSAGE_OPENSERVICE_FAILED
Severity = Informational
Language = English
Can't open service!
.
Language = French
Impossible d'ouvrir le service!
.
Language = Italian
Impossibile aprire il servizio!
.

MessageId = +1
SymbolicName = NSSM_MESSAGE_DELETESERVICE_FAILED
Severity = Informational
Language = English
Error deleting service!
.
Language = French
Erreur � la suppression du service!
.
Language = Italian
Errore durante la rimozione del servizio!
.

MessageId = +1
SymbolicName = NSSM_MESSAGE_SERVICE_REMOVED
Severity = Informational
Language = English
Service "%s" removed successfully!
.
Language = French
Le service "%s" a �t� d�sinstall� avec succ�s!
.
Language = Italian
Servizio "%s" rimosso correttamente!
.

MessageId = +1
SymbolicName = NSSM_GUI_CREATEDIALOG_FAILED
Severity = Informational
Language = English
CreateDialog() failed:
%s
.
Language = French
CreateDialog() a �chou�:
%s
.
Language = Italian
Chiamata a CreateDialog() fallita:
%s
.

MessageId = +1
SymbolicName = NSSM_GUI_MISSING_SERVICE_NAME
Severity = Informational
Language = English
No valid service name was specified!
.
Language = French
Aucun nom de service valide n'a �t� sp�cifi�!
.
Language = Italian
Nessun nome di servizio valido specificato!
.

MessageId = +1
SymbolicName = NSSM_GUI_MISSING_PATH
Severity = Informational
Language = English
No valid executable path was specified!
.
Language = French
Aucun chemin valide de fichier ex�cutable n'a �t� sp�cifi�!
.
Language = Italian
Path verso l'eseguibile non specificato!
.

MessageId = +1
SymbolicName = NSSM_GUI_INVALID_OPTIONS
Severity = Informational
Language = English
No valid options were specified!
.
Language = French
Aucun option valide n'a �t� sp�cifi�e!
.
Language = Italian
Nessuna opzione valida specificata!
.

MessageId = +1
SymbolicName = NSSM_GUI_OUT_OF_MEMORY_FOR_IMAGEPATH
Severity = Informational
Language = English
Error constructing ImagePath!\nThis really shouldn't happen.  You could be out of memory
or the world may be about to end or something equally bad.
.
Language = French
M�moire insuffisante pour sp�cifier le chemin de l'image (ImagePath)!
Cette situation ne devrait jamais se produire.  Vous �tes peut-�tre � court de m�moire RAM,
ou la fin du monde est proche, ou un autre d�sastre du m�me type.
.
Language = Italian
Errore durante la costruzione di ImagePath!\nQesto errore � inatteso. La memoria � insuffieiente
oppure il mondo sta per finire oppure � accaduto qualcosa di ugualmente grave!
.

MessageId = +1
SymbolicName = NSSM_GUI_INSTALL_SERVICE_FAILED
Severity = Informational
Language = English
Couldn't create service!
Perhaps it is already installed...
.
Language = French
Impossible de cr�er le service!
Peut-�tre est-il d�j� install�...
.
Language = Italian
Impossibile creare il servizio!
Probabilmente � gi� installato...
.

MessageId = +1
SymbolicName = NSSM_GUI_CREATE_PARAMETERS_FAILED
Severity = Informational
Language = English
Couldn't set startup parameters for the service!
Deleting the service...
.
Language = French
Impossible de r�gler les param�tres de d�marrage pour le service!
Suppression du dit service...
.
Language = Italian
Impossibile impostare i parametri di avvio per il servizio!
Eliminazione servizio in corso...
.

MessageId = +1
SymbolicName = NSSM_GUI_ASK_REMOVE_SERVICE
Severity = Informational
Language = English
Remove the service?
.
Language = French
Supprimer le service "%s" ?
.
Language = Italian
Eliminare il servizio?
.

MessageId = +1
SymbolicName = NSSM_GUI_SERVICE_NOT_INSTALLED
Severity = Informational
Language = English
Can't open service!
Perhaps it isn't installed...
.
Language = French
Impossible d'ouvrir le service!
Celui-ci n'est peut-�tre pas install�...
.
Language = Italian
Impossibile aprire il servizio!
Probabilmente non � installato...
.

MessageId = +1
SymbolicName = NSSM_GUI_REMOVE_SERVICE_FAILED
Severity = Informational
Language = English
Can't delete service!  Make sure the service is stopped and try again.
If this error persists, you may need to set the service NOT to start
automatically, reboot your computer and try removing it again.
.
Language = French
Impossible de supprimer le service!  Assurez-vous que ce service est arr�t� et r�essayez.
Si cette erreur persiste, r�glez ce service en lancement MANUEL
(non automatique), red�marrez votre ordinateur et tentez de nouveau la suppression.
.
Language = Italian
Impossibile eliminare il servizio! Verificare che sia stato fermato e riprovare.
Se l'errore persiste, provare ad impostare il servizio come avvio NON
automatico, riavviare il computer e tentare di nuovo la rimozione.
.

MessageId = +1
SymbolicName = NSSM_GUI_BROWSE_FILTER
Severity = Informational
Language = English
Applications%sAll files%s%0
.
Language = French
Applications%sTous les fichiers%s%0
.
Language = Italian
Applicazioni%sTutti i files%s%0
.

MessageId = +1
SymbolicName = NSSM_GUI_BROWSE_FILTER_APPLICATIONS
Severity = Informational
Language = English
Applications%0
.
Language = French
Applications%0
.
Language = Italian
Applicazioni%0
.

MessageId = +1
SymbolicName = NSSM_GUI_BROWSE_FILTER_ALL_FILES
Severity = Informational
Language = English
All files%0
.
Language = French
Tous les fichiers%0
.
Language = Italian
Tutti i files%0
.

MessageId = +1
SymbolicName = NSSM_GUI_BROWSE_TITLE
Severity = Informational
Language = English
Locate application file
.
Language = French
Indiquez le fichier ex�cutable
.
Language = Italian
Ricerca file applicazione
.

MessageId = 1001
SymbolicName = NSSM_EVENT_DISPATCHER_FAILED
Severity = Error
Language = English
StartServiceCtrlDispatcher() failed:
%1
.
Language = French
Erreur en tentant de connecter la t�che principale du service au gestionnaire de services Windows.
StartServiceCtrlDispatcher() a �chou�:
%1
.
Language = Italian
Chiamata a StartServiceCtrlDispatcher() fallita:
%1
.

MessageId = +1
SymbolicName = NSSM_EVENT_OPENSCMANAGER_FAILED
Severity = Error
Language = English
Unable to connect to service manager!
Perhaps you need to be an administrator...
.
Language = French
Connexion impossible au gestionnaire de services!
Il vous manque peut-�tre des droits d'administrateur.
.
Language = Italian
Impossibile connettersi al Service Manager!
Probabilmente sono necessari permessi di Amministratore...
.

MessageId = +1
SymbolicName = NSSM_EVENT_OUT_OF_MEMORY
Severity = Error
Language = English
Out of memory for %1 in %2!
.
Language = French
M�moire insuffisante pour %1 dans %2!
.
Language = Italian
Memoria insufficiente per %1 in %2!
.

MessageId = +1
SymbolicName = NSSM_EVENT_GET_PARAMETERS_FAILED
Severity = Error
Language = English
Failed to get startup parameters for service %1.
.
Language = French
Param�tres de d�marrage non trouv�s pour le service %1.
.
Language = Italian
Impossibile ottenere i permessi di avvio per il servizio %1.
.

MessageId = +1
SymbolicName = NSSM_EVENT_REGISTERSERVICECTRLHANDER_FAILED
Severity = Error
Language = English
RegisterServiceCtrlHandlerEx() failed:
%1
.
Language = French
�chec de l'enregistrement de la fonction de gestion des requ�tes �tendues de contr�le du service.
RegisterServiceCtrlHandlerEx() a �chou�:
%1
.
Language = Italian
Chiamata a RegisterServiceCtrlHandlerEx() fallita:
%1
.

MessageId = +1
SymbolicName = NSSM_EVENT_START_SERVICE_FAILED
Severity = Error
Language = English
Can't start %1 for service %2.
Error code: %3.
.
Language = French
Impossible de d�marrer %1 pour le service %2.
Code erreur: %3.
.
Language = Italian
Impossibile avviare %1 per il servizio %2.
Codice errore: %3.
.

MessageId = +1
SymbolicName = NSSM_EVENT_RESTART_SERVICE_FAILED
Severity = Warning
Language = English
Failed to restart %1 for service %2.
Sleeping...
.
Language = French
Impossible de red�marrer %1 pour le service %2.
Mise en sommeil...
.
Language = Italian
Impossibile riavviare %1 per il servizio %2.
In stato di attesa...
.

MessageId = +1
SymbolicName = NSSM_EVENT_STARTED_SERVICE
Severity = Informational
Language = English
Started %1 %2 for service %3 in %4.
.
Language = French
D�marrage r�ussi de %1 %2 pour le service %3 depuis le r�pertoire %4.
.
Language = Italian
Avviati %1 %2 per il servizio %3 in %4.
.

MessageId = +1
SymbolicName = NSSM_EVENT_REGISTERWAITFORSINGLEOBJECT_FAILED
Severity = Warning
Language = English
Service %1 may claim to be still running when %2 exits.
RegisterWaitForSingleObject() failed:
%3
.
Language = French
Le service %1 peut indiquer �tre toujours actif lorsque %2 se terminera.
RegisterWaitForSingleObject() a �chou�:
%3
.
Language = Italian
Servizio %1 potrebbe richiedere di essere in esecuzione quando %2 termina.
Chiamata a RegisterWaitForSingleObject() fallita:
%3
.

MessageId = +1
SymbolicName = NSSM_EVENT_CREATEPROCESS_FAILED
Severity = Error
Language = English
Failed to start service %1.  Program %2 couldn't be launched.
CreateProcess() failed:
%3
.
Language = French
�chec du d�marrage du service %1.  Le programme %2 n'a pas pu �tre lanc�.
CreateProcess() a �chou�:
%3
.
Language = Italian
Impossibile avviare il servizio %1.  Il programma %2 non pu� essere avviato.
Chiamata a CreateProcess() fallita:
%3
.

MessageId = +1
SymbolicName = NSSM_EVENT_TERMINATEPROCESS
Severity = Informational
Language = English
Killing process %2 because service %1 is stopping.
.
Language = French
Arr�t forc� du processus %2 du fait de l'arr�t du service %1.
.
Language = Italian
Arresto in corso del processo %2 in quanto il processo %1 sta terminando.
.

MessageId = +1
SymbolicName = NSSM_EVENT_PROCESS_ALREADY_STOPPED
Severity = Informational
Language = English
Requested stop of service %1.  No action is required as program %2 is not running.
.
Language = French
Arr�t requis du service %1.  Aucune action n'est requise car le programme %2 n'est pas en cours d'ex�cution.
.
Language = Italian
Richiesta terminazione del servizio %1.  Nessuna azione necessaria in quanto il programma %2 non � in esecuzione.
.

MessageId = +1
SymbolicName = NSSM_EVENT_ENDED_SERVICE
Severity = Informational
Language = English
Program %1 for service %2 exited with return code %3.
.
Language = French
Le programme %1 pour le service %2 s'est arr�t� avec code retour %3.
.
Language = Italian
Il programma %1 per il servizio %2 � terminato con codice errore %3.
.

MessageId = +1
SymbolicName = NSSM_EVENT_EXIT_RESTART
Severity = Informational
Language = English
Service %1 action for exit code %2 is %3.
Attempting to restart %4.
.
Language = French
L'action pr�vue du service %1 pour le code retour %2 est: %3.
Tentative de red�marrage de %4.
.
Language = Italian
L'azione per il servizio %1, codice di uscita %2, � %3
Tentativo di riavvio %4.
.

MessageId = +1
SymbolicName = NSSM_EVENT_EXIT_IGNORE
Severity = Informational
Language = English
Service %1 action for exit code %2 is %3.
No action will be taken to restart %4.
.
Language = French
L'action pr�vue du service %1 pour le code retour %2 est: %3.
Aucune action ne sera entreprise pour red�marrer %4.
.
Language = Italian
Azione di servizio "%1" per il codice di uscita %2 � %3.
Nessuna azione sar� intrapresa per riavviare %4.
.

MessageId = +1
SymbolicName = NSSM_EVENT_EXIT_REALLY
Severity = Informational
Language = English
Service %1 action for exit code %2 is %3.
Exiting.
.
Language = French
L'action pr�vue du service %1 pour le code retour %2 est: %3.
Le programme ne sera pas red�marr�.
.
Language = Italian
L'azione per il servizio %1, codice di uscita %2, � %3.
Termine.
.

MessageId = +1
SymbolicName = NSSM_EVENT_OPENKEY_FAILED
Severity = Error
Language = English
Failed to open registry key HKLM\%1:
%2
.
Language = French
�chec de l'ouverture de la cl� de registre HKLM\%1:
%2
.
Language = Italian
Impossibile aprire la chiave di registro HKLM\%1:
%2
.

MessageId = +1
SymbolicName = NSSM_EVENT_QUERYVALUE_FAILED
Severity = Error
Language = English
Failed to read registry value %1:
%2
.
Language = French
�chec de l'ouverture de la valeur de registre %1:
%2
.
Language = Italian
Impossibile leggere la chiave di registro %1:
%2
.

MessageId = +1
SymbolicName = NSSM_EVENT_SETVALUE_FAILED
Severity = Error
Language = English
Failed to write registry value %1:
%2
.
Language = French
�chec de l'�criture de la valeur de registre %1:
%2
.
Language = Italian
Impossibile scrivere la chiave di registro %1:
%2
.

MessageId = +1
SymbolicName = NSSM_EVENT_EXIT_UNCLEAN
Severity = Informational
Language = English
Service %1 action for exit code %2 is %3.
Exiting.
.
Language = French
L'action pr�vue du service %1 pour le code retour %2 est: %3.
Le programme s'est termin� de mani�re impropre.
.
Language = Italian
L'azione per il servizio %1, codice di uscita %2, � %3.
Il programma � terminato in maniera impropria.
.

MessageId = +1
SymbolicName = NSSM_EVENT_GRACEFUL_SUICIDE
Severity = Informational
Language = English
Service %1 application %2 exited with exit code 0 but the default exit action is %3.
Honouring the %4 action would result in the service being flagged as failed and subject to recovery actions.
The service will instead be stopped gracefully.  To suppress this message, explicitly configure the exit action for exit code 0 to either %5 or %6.
.
Language = French
L'application %2 du service %1 s'est termin�e sur un code retour 0.  Par d�faut, lorsque l'application se termine, l'action suivante est configur�e: %3.
Ex�cuter cette action %4 ferait que le service serait marqu� en �chec et sujet � des actions de r�cup�ration.
Donc, pour �viter cette situation, le service sera arr�t� normalement.
Pour supprimer le pr�sent message, configurez explicitement l'action de sortie pour le code retour 0 � %5 ou %6.
.
Language = Italian
Servizio %1 applicazione %2 uscita con docide 0 ma l'azione di uscita di default �%3.
In base all'azione %4 il servizio andrebbe impostato come fallito e soggetto ad azioni di ripristino.
Il servizio verr� invece terminato in modo gentile. Per eliminare questo messaggio, impostare l'azione di uscita per il codice di uscita 0 su %5 o %6.
.

MessageId = +1
SymbolicName = NSSM_EVENT_EXPANDENVIRONMENTSTRINGS_FAILED
Severity = Error
Language = English
Failed to expand registry value %1:
%2
.
Language = French
Erreur lors de l'expansion des variables d'environnement dans la valeur de registre %1:
%2
.
Language = Italian
Impossibile espandere la chiave di registro %1:
%2
.

MessageId = +1
SymbolicName = NSSM_EVENT_KILLING
Severity = Informational
Language = English
Killing process tree of process %2 for service %1 with exit code %3
.
Language = French
Interruption du processus %2 et de ses processus-fils pour le service %1. Code retour = %3
.
Language = Italian
Arresto dell'albero di processo %2 per il servizio %1 con codice di uscita %3
.

MessageId = +1
SymbolicName = NSSM_EVENT_CREATETOOLHELP32SNAPSHOT_PROCESS_FAILED
Severity = Error
Language = English
Failed to create snapshot of running processes when terminating service %1:
%2
.
Language = French
Impossible de cr�er un instantan� des processus en cours d'ex�cution lors de l'arr�t du service %1:
%2
.
Language = Italian
Impossibile creare uno snapshot dei processi in esecuzione durante l'arresto del servizio %1!
%2
.

MessageId = +1
SymbolicName = NSSM_EVENT_PROCESS_ENUMERATE_FAILED
Severity = Error
Language = English
Failed to enumerate running processes when terminating service %1:
%2
.
Language = French
Impossible d'�num�rer les processus en cours d'ex�cution lors de l'arr�t du service %1:
%2
.
Language = Italian
Impossibile enumerare i processi in esecuzione durante la terminazione del servizio %1.
%2
.

MessageId = +1
SymbolicName = NSSM_EVENT_OPENPROCESS_FAILED
Severity = Error
Language = English
Failed to open process handle for process with PID %1 when terminating service %2:
%3
.
Language = French
�chec � l'ouverture du handle de processus avec PID est %1 lors de l'arr�t du service %2:
%3
.
Language = Italian
Impossibile aprire l'handle di proceso con PID %1 durante la terminazione del servizio %2.
%3
.

MessageId = +1
SymbolicName = NSSM_EVENT_KILL_PROCESS_TREE
Severity = Informational
Language = English
Killing PID %1 in process tree of PID %2 because service %3 is stopping.
.
Language = French
Arr�t forc� du processus avec PID %1 (processus enfant du processus avec PID %2) r�sultant de l'arr�t du service %3.
.
Language = Italian
Terminazione del PID %1 nell'albero di processo con PID %2 in quanto il servizio %3 � in fase di terminazione.
.

MessageId = +1
SymbolicName = NSSM_EVENT_TERMINATEPROCESS_FAILED
Severity = Error
Language = English
Failed to terminate process with PID %1 for service %2:
%3
.
Language = French
Impossible d'arr�ter le processus avec PID %1 pour le service %2:
%3
.
Language = Italian
Impossibile terminare il processo con PID %1 per il servizio %2:
%3
.

MessageId = +1
SymbolicName = NSSM_EVENT_NO_FLAGS
Severity = Warning
Language = English
Registry key %1 is unset for service %2.
No flags will be passed to %3 when it starts.
.
Language = French
La cl� de registre %1 n'est pas d�finie pour le service %2.
Aucune option ne sera transmise � %3 lorsqu'il d�marrera.
.
Language = Italian
La chiave di registro %1 non � impostata per il servizio %2.
Nessin flag verr� passato a %3 in fase di avvio.
.

MessageId = +1
SymbolicName = NSSM_EVENT_NO_DIR
Severity = Warning
Language = English
Registry key %1 is unset for service %2.
Assuming startup directory %3.
.
Language = French
La cl� de registre %1 n'est pas d�finie pour le service %2.
Le r�pertoire de d�marrage sera suppos� �tre: %3.
.
Language = Italian
La chiave di registro %1 non � impostata per il servizio %2.
Cartella di avvio presunta: %3.
.

MessageId = +1
SymbolicName = NSSM_EVENT_NO_DIR_AND_NO_FALLBACK
Severity = Error
Language = English
Registry key %1 is unset for service %2.
Additionally, ExpandEnvironmentStrings("%%SYSTEMROOT%%") failed when trying to choose a fallback startup directory.
.
Language = French
La cl� de registre %1 n'est pas d�finie pour le service %2.
De surcro�t, l'expansion de la variable d'environnement "%%SYSTEMROOT%%" a �chou� lors de la d�termination d'un r�pertoire de d�marrage de secours.
.
Language = Italian
La chiave di registro %1 non � impostata per il servizio %2.
Inoltre, la chiamata a ExpandEnvironmentStrings("%%SYSTEMROOT%%") � fallita in fase di scelta directory alternativa.
.

MessageId = +1
SymbolicName = NSSM_EVENT_CREATETOOLHELP32SNAPSHOT_THREAD_FAILED
Severity = Error
Language = English
Failed to create snapshot of running threads when terminating service %1:
%2
.
Language = French
Impossible de cr�er un instantan� des threads en cours d'ex�cution lors de l'arr�t du service %1:
%2
.
Language = Italian
Impossibile creare uno snapshot dei thread attivi dutante la fase di terminazione del servizio %1:
%2
.

MessageId = +1
SymbolicName = NSSM_EVENT_THREAD_ENUMERATE_FAILED
Severity = Error
Language = English
Failed to enumerate running threads when terminating service %1:
%2
.
Language = French
Impossible d'�num�rer les t�ches (threads) en cours d'ex�cution lors de l'arr�t du service %1:
%2
.
Language = Italian
Impossibile enumerare i thread attivi durante la fase di terminazione del servizio %1:
%2
.

MessageId = +1
SymbolicName = NSSM_EVENT_THROTTLED
Severity = Warning
Language = English
Service %1 ran for less than %2 milliseconds.
Restart will be delayed by %3 milliseconds.
.
Language = French
Le service %1 est rest� actif durant moins de %2 millisecondes.
Son red�marrage sera retard� de %3 millisecondes.
.
Language = Italian
Il servizio %1 � rimasto in esecuzione per meno di %2 millisecondi.
Il riavvio verr� posticipato di %3 millisecondi.
.

MessageId = +1
SymbolicName = NSSM_EVENT_RESET_THROTTLE
Severity = Informational
Language = English
Request to resume service %1.  Throttling of restart attempts will be reset.
.
Language = French
Demande de red�marrage du service %1.  La r�gulation des tentatives de red�marrage sera r�initialis�e.
.
Language = Italian
Richiesta di cambio nome per il servizio %1. Il meccanismo di regolazione della pausa di riavvio verr� resettato.
.

MessageId = +1
SymbolicName = NSSM_EVENT_BOGUS_THROTTLE
Severity = Warning
Language = English
The registry value %2, used to specify the minimum number of milliseconds which must elapse before service %1 is considered to have started successfully, was not of type REG_DWORD.  The default time of %3 milliseconds will be used.
.
Language = French
La valeur de registre %2, indiquant le nombre minimal de millisecondes devant s'�couler avant que le service %1 soit consid�r� comme ayant d�marr� avec succ�s, 
n'�tait pas du type REG_DWORD.  Une dur�e de %3 millisecondes sera utilis�e par d�faut.
.
Language = Italian
La chiave di registro %2, utilizzata per specificare il minimo numero di millisecondi che devono intercorrere prima che il servizio %1 sia considerato avviato correttamente, non � di tipo REG_DWORD.
Verr� usato il tempo di default pari a 3 ms.
.

MessageId = +1
SymbolicName = NSSM_EVENT_CREATEWAITABLETIMER_FAILED
Severity = Warning
Language = English
Failed to create waitable timer for service %1:
%2
Throttled restarts will not be interruptible.
.
Language = French
Impossible de cr�er un d�clenchement temporis� ("waitable timer") pour le service %1:
%2
Les red�marrages r�gul�s ne pourront pas �tre interrompus.
.
Language = Italian
Impossibile creare un timer per il servizio %1:
%2
Il meccanismo di regolazione della pausa di riavvio non sar� interrompibile.
.

MessageId = +1
SymbolicName = NSSM_EVENT_CREATEPROCESS_FAILED_INVALID_ENVIRONMENT
Severity = Error
Language = English
Failed to start service %1.  Program %2 couldn't be launched.
CreateProcess() failed with ERROR_INVALID_PARAMETER and a process environment was set in the %3 registry value.  It is likely that the environment was incorrectly specified.  %3 should be a REG_MULTI_SZ value comprising strings of the form KEY=VALUE.
.
Language = French
�chec de d�marrage du service %1.  Le programme %2 n'a pas pu �tre lanc�.
La fonction CreateProcess() a �chou� sur une erreur ERROR_INVALID_PARAMETER et un environnement de processus a �t� d�fini dans la valeur de base de registre %3.
Il est vraisemblable que l'environnement a �t� sp�cifi� de mani�re incorrecte.
%3 devrait �tre d�finie comme valeur REG_MULTI_SZ comprenant des cha�nes sous la forme KEY=VALUE.
.
Language = Italian
Impossibile riavviare il servizio %1. Il programma %2 non pu� essere avviato.
Chiamata a CreateProcess() fallita con ERROR_INVALID_PARAMETER e ambiente di processo impostato nella chiave di registro %3. E' probabile che l'ambiente si stato specificato in modo errato.
$3 dovrebbe essere un valore REG_MULTI_SZ comprendente stringhe nella forma CHIAVE=VALORE.
.

MessageId = +1
SymbolicName = NSSM_EVENT_INVALID_ENVIRONMENT_STRING_TYPE
Severity = Warning
Language = English
Environment declaration %1 for service %2 is not of type REG_MULTI_SZ and will be ignored.
.
Language = French
La d�claration de l'environnement %1 pour le service %2 n'est pas du type REG_MULTI_SZ.  Cette d�claration sera ignor�e.
.
Language = Italian
Dichiarazione di ambiente %1 per il servizio %2 non � di tipo REG_MULTI_SZ e verr� quindi ingnorata.
.

MessageId = +1
SymbolicName = NSSM_EVENT_SERVICE_CONTROL_HANDLED
Severity = Informational
Language = English
Service %1 received %2 control, which will be handled.
.
Language = French
Le service %1 a re�u le code de contr�le %2, qui sera pris en compte.
.
Language = Italian
Il servizio %1 ha ricevuto la chiave di controllo %2 che sar� gestita.
.

MessageId = +1
SymbolicName = NSSM_EVENT_SERVICE_CONTROL_NOT_HANDLED
Severity = Informational
Language = English
Service %1 received unsupported %2 control, which will not be handled.
.
Language = French
Le service %1 a re�u le code de contr�le %2, qui n'est pas g�r�.  Aucune action ne sera entreprise en r�ponse � cette demande.
.
Language = Italian
Il servizio %1 ha ricevuto una chiave di controllo %2 non supportata. Essa non sar� gestita.
.

MessageId = +1
SymbolicName = NSSM_EVENT_SERVICE_CONTROL_UNKNOWN
Severity = Informational
Language = English
Service %1 received unknown service control message %2, which will be ignored.
.
Language = French
Le service %1 a re�u le code de contr�le inconnu %2, qui sera donc ignor�.
.
Language = Italian
Il servizio %1 ha ricevuto un messaggio di controllo di servizio sconosciuto %2, che verr� ignorato.
.

MessageId = +1
SymbolicName = NSSM_EVENT_CHANGESERVICECONFIG2_FAILED
Severity = Informational
Language = English
Error configuring service failure actions for service %1.  The service will not be subject to recovery actions if it exits gracefully with a non-zero exit code.
ChangeServiceConfig2() failed:
%2
.
Language = French
Erreur lors de la configuration des actions en cas d'�chec du service %1.  Le service ne d�clenchera aucune action de r�cup�ration s'il se termine normalement avec un code retour non nul.
ChangeServiceConfig2() a �chou�:
.
Language = Italian
Errore in fase di configurazione delle aziondi di fallimento per il servizio %1. Il servizio non sar� soggetto ad azioni di ripristino nel caso termini in modo gentile con un codice di uscita non nullo.
Chiamata a ChangeServiceConfig2() fallita:
%2
.

MessageId = +1
SymbolicName = NSSM_EVENT_GETPROCESSTIMES_FAILED
Severity = Error
Language = English
GetProcessTimes() failed:
%1
.
Language = French
�chec de GetProcessTimes():
%1
.
Language = Italian
Chiamata a GetProcessTimes():
%1
.
