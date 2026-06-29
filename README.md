# BookstoriesLLM
Progetto marketplace di Libri con ausilio di LLM per LTDW 2026 

## Comparative Report

### 1. Executive Summary

#### Fase 1
Il progetto fase 1 e' stato implementato e costruito usando il processo iterativo per slice.
Ogni fase del progetto e' stata infatti suddivisa in una fetta composta da: 
- una parte del DB. (come ad esempio la sezione dedicata agli utenti)
- le rispettive operazioni CRUD
- e le rispettive e moduli delle pagine del frontend.
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
