# BookstoriesLLM
Progetto marketplace di Libri con ausilio di LLM per LTDW 2026 

## Comparative Report

### 1. Executive Summary

#### Fase 1
Il progetto fase 1 e' stato implementato e costruito usando il processo iterativo per slice.
Ogni fase del progetto e' stata infatti suddivisa in una fetta composta da: 
- una parte del DB. (come ad esempio la sezione dedicata agli utenti)
- le rispettive operazioni CRUD
- e le rispettive parti nelle pagine del frontend.
La gestione a slice del progetto ha garantito velocita' nella programmazione e soprattutto la possibilita' di testare velocemente le varie parti sviluppate.
Una parte di grande complessita' e' stata trovare la giuste template che, soprattutto per il caso del frontend, ha causato il cambio di dominio del sito dopo un primo tentativo.
La scelta delle template infine e' ricaduta su:
- Fruitables per il frontend
- Tabler (1.0.0) per il back.

#### Fase 2
Il progetto fase 2 e' una versione molto simile del sito fase 1 ma con alcune differenze, tra cui:
- una home rivisitata ed aumentata
- gestione simulata dell'inserimento dei dati nel checkout
- miglioramenti visivi in generale sia su front che dashboard

#### Key findings
Durante la fase 1, con anche l'utilizzo di slice ben separati di implementazione, la risoluzione degli errori e' risultata decisamente piu' semplice.
Durante la fase 2, proprio a causa dell'uso di LLM, si sono create situazioni con errori (avvolte anche molto semplici) che hanno richiesto spesso molto tempo per la risoluzione.
Nonostante cio' si e' guadagnato molto tempo nell'implementazione finale del progetto.

### 2. Application Description
L'applicazione e' un e-commerce di libri.
Gli utenti target del sito sono persone che cercano nello specifico libri, spesso anche di nicchia, che non trovano in negozio fisico.

### 3. Development Process Comparison
Nello sviluppo della seconda fase (LLM) nonostante si fosse iniziato con l'idea di sviluppare per slice si e' reso subito comodo continuare per interi blocchi di sviluppo, in quanto piu' semplice per il ragionamento dell'LLM nel riuscire a seguire il contesto del progetto.

### 4. Code Quality Analysis
Rispetto alla fase 1, la fase 2 risulta decisamente piu' leggibile e si e' riuscito ad ottenere un codice piu' pulito e con miglior separazione logica.
Per quanto riguarda la struttura:

Nel primo progetto ho utilizzato una struttura semplice con in alcuni casi metodi con tanti parameteri, come ad esempio:
```PHP
create($title, $isbn, $price, $stock, $description, $cover)
```


Mentre nel secondo progetto sono riuscito ad ottenere il tutto in modo piu' pulito, ad esempio:
```PHP
create($data)
```

Il tutto risulta molto piu' leggibile grazie anche a numerosi commenti molto piu' specifici e soprattutto a nomi piu' centrati.
Inoltre tutto il progetto fase 2 e' piu' robusto nella gestione degli errori.
Sempre il secondo progetto, inoltre, e' strutturato meglio, utilizzando PHP Data Object con prepared statements in modo consistente.

### 5. Feature Comparison
Nella fase 2 molto risulta simile al sito fase 1.
Le principali differenze si possono trovare ad esempio nella schermata home, che risulta piu' professionale e piu' viva.
Sempre nella parte pubblica del sito il cart e checkout risultano piu' puliti visivamente.
Per quanto riguarda il checkout, qui e' stato aggiunto (in modo simulativo) l'inserimento dei dati della carta e di spedizione.
Per quanto riguarda la dashboard, la sezione principale contiene info piu utili ed il tutto risulta meglio organizzato.

### 6. LLM
E' stato scelto Gemini in quanto LLM con limiti di utilizzo piu' ampi e quindi possibilita' di utilizzare piu contesto possibile durante le varie fasi di sviluppo.
Nonostante i limiti piu' ampi, spesso gli errori dell'LLM erano dovuti proprio a casi in cui non utilizzava il contesto dato, oppure a semplice confusione creata dallo stesso LLM durante il ragionamento.
Un esempio di errori ripetuti lo si e' trovato direttamente nel codice sql, con spesso tabelle inventate dovute probabilmente a "deduzione" incorretta.
Spesso inoltre trovava problemi nell'uso corretto di `template2.inc.php`.

### 7. Effort and Productivity
Non ho una stima precisa delle tempistiche di ciascuna fase. 
Ma risulta enorme la differenza di produttivita' percepita.
Infatti durante la fase 2 il tutto e' risultato piu' semplice da implementare e testare, seppur con un costo di correzione degli errori piu' alto rispetto alla fase 1 in termini di tempo, ma comunque accettabile.

### 8. Critical Reflection
Il confronto tra i due approcci mi ha fatto capire che la conoscenza acquisita durante lo sviluppo manuale della prima fase e' stata fondamentale per utilizzare in modo efficace l'LLM nella seconda.
Realizzare inizialmente il progetto senza LLM mi ha permesso di comprendere meglio la struttura completa di una applicazione web, l'organizzazione del codice tra frontend, backend e db, e soprattutto il flusso dei dati tra i vari componenti dell'applicazione.
Questo ha aiutato molto durante la ricerca e risoluzione degli errori generati dall'LLM.
Sicuramente la fase 1 ha aiutato molto anche nel riuscire a comprendere se una soluzione proposta dall'LLM fosse realmente e tecnicamente corretta oppure no.
Aver sviluppato il progetto manualmente ha cambiato anche il modo in cui interagivo con LLM nel secondo. Invece di chiedere semplicemente di generare codice, riuscivo a fornire un giusto contesto preciso e richiedere modifiche mirate. 

Ritengo che il risultato sarebbe stato diverso se avessi utilizzato LLM fin da subito. Probabilmente lo sviluppo iniziale sarebbe stato piu' rapido, ma avrei avuto maggiori difficolta' nel valutare la correttezza delle soluzioni proposte (e nell'individuare errori).

### 9. Conclusions
In conclusione, una buona comprensione dello sviluppo web rimane fondamentale sia per formulare richieste efficaci sia per verificare criticamente il codice generato.
In generale, considerando la diffusione e la maturita' raggiunta dagli LLM, ritengo che non utilizzarli nello sviluppo web significhi rinunciare a uno strumento in grado di aumentare significativamente la produttivita'.
Tuttavia, il loro utilizzo dovrebbe essere visto come un supporto al lavoro dello sviluppatore e non come un sostituto delle competenze tecniche.
Per ottenere risultati utilizzabili e' necessario saper guidare il modello, fornire il contesto corretto e possedere una conoscenza tale da riconoscere e correggere eventuali errori o soluzioni non adeguate.


# ER Diagram Fase 2
<img width="6134" height="3374" alt="ER DIAGRAM BOOKSTORIES LLM" src="https://github.com/user-attachments/assets/9a14c1ba-7494-443c-b049-66a995c90284" />



# Prompt

1. Agisci come un Web Developer con risposte concise. Dobbiamo sviluppare da zero un e-commerce per una libreria in PHP e MySQL, seguendo il pattern di separazione della logica con 'template2.inc.php'. Come primo step, scrivi un file SQL per la creazione del database. Deve contenere almeno 14 tabelle, includendo il pattern obbligatorio utenti-gruppi-servizi, oltre alla gestione di libri, autori, categorie, editori, ordini e carrello. Usa vincoli di integrità e `ON DELETE CASCADE` dove appropriato.

2. Crea un file `Database.php` che gestisca la connessione a MySQL utilizzando PDO. Implementa il pattern Singleton per evitare connessioni multiple e gestisci le eccezioni in modo silenzioso per non esporre dati sensibili.


 3. Basandoti sullo schema SQL appena creato, scrivi la classe `BookDAO.php`. Implementa tutti i metodi CRUD (Create, Read, Update, Delete) e i metodi per recuperare i libri per categoria o autore. Usa esclusivamente prepared statements per prevenire SQL Injection.
<img width="614" height="277" alt="Pasted image 20260625150046" src="https://github.com/user-attachments/assets/c94807a0-d4ba-4d3e-9db0-fa51d482db22" />

 
 4. Ora scrivi le classi `UserDAO.php` e `OrderDAO.php`. `UserDAO` deve gestire la registrazione, la verifica delle credenziali e le query relative a gruppi e servizi. `OrderDAO` deve gestire la creazione di un ordine e dei suoi `order_items`. Assicurati che il codice sia fortemente tipizzato e pulito
<img width="621" height="359" alt="Pasted image 20260625150513" src="https://github.com/user-attachments/assets/020eace1-540d-4f38-a843-08247e826f1c" />


5. Sviluppa il sistema di Session Management. Crea un file `auth_functions.php` con le funzioni per gestire il login, il logout e un meccanismo di controllo degli accessi (Access Control) basato sui servizi dell'utente. Usa `password_hash()` e `password_verify()`.

6. Iniziamo il Backend. Usando 'template2.inc.php', scrivi lo script PHP e usando relativo template per la Dashboard di amministrazione. La dashboard deve riepilogare il totale degli ordini, degli utenti e dei libri. Mantieni il codice PHP libero da tag HTML. 

7. Scrivi la pagina di amministrazione dei Libri (lista e form di inserimento/modifica). Usa i DAO precedentemente creati. Implementa una validazione lato server robusta sui dati in input (prezzi, ISBN, etc.) e gestisci l'upload sicuro dell'immagine di copertina.
<img width="614" height="370" alt="Pasted image 20260625152349" src="https://github.com/user-attachments/assets/25e069e6-cf11-4ec0-8e47-da5659833aee" />


8. Aggiungi le pagine di gestione per Utenti, Gruppi e Ordini nel backend. L'amministratore deve poter assegnare un utente a un gruppo(admin o cliente) e cambiare lo stato di un ordine (es. da pending a shipped)
<img width="594" height="250" alt="Pasted image 20260625152905" src="https://github.com/user-attachments/assets/b6645614-6c91-4343-99a0-9f6cf4e5ef34" />


9. Passiamo al Frontend. Crea la `index.php` (Home) e la pagina di catalogo `books.php`. Mostra i libri in vetrina nella home. Nel catalogo, implementa un sistema di ricerca e filtri (es. per categoria). Usa 'template2.inc.php' per caricare un layout moderno e responsive.
<img width="589" height="106" alt="Pasted image 20260625153410" src="https://github.com/user-attachments/assets/9a395191-2951-4eaf-b8dd-f108a478b467" />


10. Lavora su home.html e main.html. Rendimi uniformi le varie card e rendi semplice l'esplorazione del catalogo per i non loggati. Inoltre occupati di books.html e sistemalo con la form di ricerca. 
<img width="582" height="75" alt="Pasted image 20260625153653" src="https://github.com/user-attachments/assets/cbf8444b-6013-48e2-9ef9-66304e020abf" />


11. Scrivi la logica e i template per la visualizzazione del singolo libro (`book_detail.php`), includendo il pulsante per aggiungere al carrello
<img width="637" height="340" alt="Pasted image 20260625154102" src="https://github.com/user-attachments/assets/04022203-11a5-48ef-b25e-0a4f7dcaf58f" />


12. Implementa la logica del Carrello e del Checkout (`cart.php` e `checkout.php`). L'utente deve poter aggiornare le quantità. Al momento del checkout, salva l'ordine nel database calcolando il totale corretto usando i DAO.
<img width="630" height="320" alt="Pasted image 20260625154450" src="https://github.com/user-attachments/assets/35799529-566b-42d7-99e7-0bedb17dae48" />


13. Aggiungi le pagine mancanti login.php, logout.php, profile.php, shop.php e i template frontend login.html, profile.html. Aggiorna UserDAO.php con `getByEmail()` e `getById()`, correggi i riferimenti a `CartDAO`, login.php, logout.php, profile.php e shop.php, e verifica la sintassi PHP usando php.exe. Restituisci il riepilogo delle modifiche fatte.
<img width="455" height="435" alt="Pasted image 20260625162320" src="https://github.com/user-attachments/assets/427110be-9fef-468c-aeb7-f45f1769811d" />


14. E' presente errore nel collegaamento alla dashboard, essendo un admin.
<img width="437" height="57" alt="Pasted image 20260625170024" src="https://github.com/user-attachments/assets/e8bcaded-79c8-47e9-843e-c2baa6621754" />


15. Nella dashboard risultano problemi di formattazione e l'assenza del tasto per poter tornare all'indice del sito pubblico. Inoltre va sistemata la possibilita di eliminare un utente.

16. Risultano problemi con le operazioni crud su libri nel qual mancano elementi come editore, autore, genere. Aggiungiamole in modo dettagliato nella dashboard
<img width="706" height="149" alt="Pasted image 20260625182419" src="https://github.com/user-attachments/assets/eede5a5d-64f0-4c27-808e-f2b9f0978925" />


17.  Inseriamo sistema di pagamenti fittizio con: validazione, pagamento(simulato), logistica (salva indirizzo nel db). Tenendo conto di tutte quelle che gia sono le funzionalita legate alla zona checkout.
<img width="603" height="253" alt="Pasted image 20260625185322" src="https://github.com/user-attachments/assets/934ab85a-1883-4c74-941b-1bfe63993e63" />


# Development Diary

### Lunedì 25 maggio

La prima bozza non molto buona: il pattern utenti-gruppi-servizi era abbozzato male. 
Ho dovuto rispiegare bene lo schema users/groups/services e chiedere di scomporre editori, autori e categorie in tabelle separate invece di campi testuali.

**Cosa ha funzionato:** schema.sql con 17 tabelle, vincoli FK e ON DELETE CASCADE sulle tabelle di relazione, importato su MySQL senza errori.

### Martedì 26 maggio

Ho chiesto la classe `Database.php` con pattern Singleton via PDO. 

**Cosa ha funzionato:** Singleton + PDO. Eccezioni gestite senza esporre nulla all'utente, connessione testata.


### Giovedì 28 maggio

Lavoro su `BookDAO.php` (CRUD libri + ricerca per categoria/autore).

**Cosa ha funzionato:** metodi CRUD su libri testati con dati finti.

### Sabato 30 maggio

Lavoro su `UserDAO.php` + `OrderDAO.php`, session management con `auth_functions.php`, e prima bozza della dashboard di amministrazione.

Non tutto corretto. Su `auth_functions.php` il controllo accessi inizialmente verificava solo "sei loggato o no", non i permessi per servizio. Quindi un cliente normale avrebbe potuto raggiungere pagine riservate digitando l'URL giusto.

**Cosa ha funzionato:** UserDAO/OrderDAO con totali ricalcolati server-side, login/logout con sessioni e controllo accessi reale per servizio + dashboard con i tre contatori.

### Domenica 31 maggio

Pagina admin per i libri (lista + form + upload copertina) e pagine di gestione Utenti/Gruppi/Ordini.

Sull'upload immagini la prima versione accettava qualsiasi file senza controllare. Sistemato con whitelist di estensioni e controllo dimensione. Sulla gestione gruppi invece il form per assegnare un utente a un gruppo si inviava ma non aggiornava davvero `users_has_groups`, il metodo nel DAO non era collegato bene al form.

**Cosa ha funzionato:** upload copertine sicuro e validato, validazione server-side su prezzo/ISBN, assegnazione utente-gruppo funzionante, cambio stato ordine (pending --> shipped) persistente.

### Martedì 2 giugno

Lavoro su `index.php` (home con vetrina libri) e `books.php` (catalogo con ricerca/filtri). Il filtro per categoria nella prima versione era decorativo: la query non veniva mai filtrata davvero, sempre tutti i libri a schermo a prescindere dalla selezione.

Corretto facendo notare che il parametro categoria non arrivava alla query nel DAO.

**Cosa ha funzionato:** home con libri in vetrina, catalogo con ricerca testuale e filtro categoria realmente funzionanti.


### Giovedì 4 giugno

Ritocco a `home.html`/`main.html` per uniformare le card (dimensioni e border-radius diversi tra home e catalogo) e revisione di `books.html`
Lavoro su pagina di dettaglio libro `book_detail.php` con pulsante "aggiungi al carrello", e subito dopo la logica di carrello e checkout (`cart.php`, `checkout.php`).

**Cosa ha funzionato:** Card coerenti su tutte le pagine pubbliche, layout responsive, form di ricerca inserita meglio nel design. Pagina dettaglio completa con gestione corretta, carrello che aggiorna i totali in tempo reale, checkout che salva l'ordine con l'importo corretto calcolato server-side.


### Domenica 7 giugno

Lavoro su pagine ancora mancanti (`login.php`, `logout.php`, `profile.php`, `shop.php`) più i template `login.html`/`profile.html`, aggiunta di `getByEmail()` e `getById()` a `UserDAO`.
Sistemazione di alcuni riferimenti rotti a `CartDAO`.

**Cosa ha funzionato:** tutte le pagine mancanti completate e collegate tra loro, sintassi verificata senza errori.

### Lunedì 8 giugno

Bug fix: link alla dashboard rotto per gli utenti admin (controllo del ruolo sbagliato in un punto della navigazione), problemi di formattazione CSS nella dashboard e assenza di un pulsante per tornare al sito pubblico.

**Cosa ha funzionato:** dashboard accessibile e ben formattata con pulsante di ritorno al sito.

### Martedì 9 giugno

Form libri nel backend ancora troppo basilare: editore, autore e genere erano campi testo liberi invece di essere collegati alle rispettive tabelle.

**Cosa ha funzionato:** form libri completo con editore/autore/genere selezionabili da menu collegati (coerente con lo schema relazionale del database).

### Mercoledì 10 giugno

Implementato il sistema di pagamento fittizio: validazione dati di pagamento, simulazione dell'esito, e logistica con salvataggio dell'indirizzo di spedizione, integrando tutto nel flusso di checkout già esistente.
Fatto un giro completo di test su tutto il sito (registrazione --> login --> catalogo --> carrello --> checkout -->pagamento --> dashboard admin) e commit finale.

**Cosa ha funzionato:** sistema di pagamento fittizio integrato con validazione e esito simulato, flusso di checkout completo e funzionante dall'inizio alla fine.




