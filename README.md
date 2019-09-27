# KEOPS (Keen Evaluator Of Parallel Sentences)

KEOPS (Keen Evaluation Of Parallel Sentences) provides a complete tool for manual evaluation of parallel sentences.

This is an overview of the application. Please refer to the [Evaluator Guide](/evaluators.md) or the [Administrator Guide](/administrators.md) for detailed information.

If you want to __deploy KEOPS__, please refer to the [Installation Guide](INSTALLATION.md).

<img src="screenshots/guide/home.png" width="700" />

## Table of Contents ##
 * [Overview](#overview)  
   * [Admins](#admins)  
   * [Evaluators](#evaluators)
 * [DB Schema](#DB)
 * [FAQ: Frequently Asked Questions](#faq)
   * [Which format must corpora have to be properly uploaded to Keops?](#faq-corpora)
   * [Which languages are preloaded in Keops?](#faq-langs)
   * [What are "validation guidelines"?](#guidelines)
   * [Which format does the "task summary" file have?](#summary-format)
   * [Which format does the "annotated sentences" file have?](#annotation-format)


<a name="overview"></a>

## Overview ##

There are two main types of users in Keops: **Users** (also known as "Evaluators") and **Admins**.
Admins can perform management tasks (such as creating tasks, uploading corpora to evaluate, invite users...) and evaluation tasks,
while regular Users can only perform evaluation tasks (assigned by the Admins).

<a name="admins"></a>

### Admins ###
The most common **workflow** for an **Admin** is as follows: 

First, the Admin **invites** the evaluators to Keops, if they are not Keops users yet. For this, the Admin just needs their email addresses. The **invitation token** generated by Keops will be automatically sent to them.

The invitation token, together with the user email, is what an user needs to create its account.

Then the admin **uploads** one (or more) **corpus**, indicating the source (except for Fluency evaluation tasks) and target languages. Most common EU languages are pre-loaded, but Admins can add new ones beforehand, if needed.

<img src="screenshots/corpora-page.png" width="700">

After these two first steps, the Admin creates a new **Project**, indicating a name and a description.

<img src="screenshots/management-page.png" width="700" />

When the project is created, the Admin can create **Tasks** in the Project. 
For this, the Admin just needs to indicate the source  (except for Fluency evaluation tasks) and target languages, the **evaluator** and the **corpus** to be evaluated.

Please note that only users and corpora matching the task languages will be available as choice.

When you assign a task to an evaluator, they will be notified via email.

<img src="screenshots/tasks-project-page.png" width="700">


Once the task is created, Evaluators can immediately start working on them.
When an Evaluator finishes evaluating all sentences from a task, and tags it as **DONE**, both the Admin and the evaluator are able to **download** the stats of the evaluation (a TSV file containing statistics about the task) and the annotated corpus (a TSV file containing the sentences and their evaluation). The Admin will be notified via email when an evaluator finishes a task.

<img src="screenshots/guide/recap-val.png" width="700">

<a name="evaluators"></a>

### Evaluators ###

The common **workflow** for an **Evaluator** is simpler:

After getting an **invitation** (an invitation token, together with the evaluator's email), the Evaluator **signs up** and creates an account.
It's important that Evaluators include their languages, so Admins can assign tasks to them. 

<img src="screenshots/signup-page.png" width="700">


If the Evaluator already had an account in Keops, instead of signing up, the Evaluator just needs to **sign in**.

<img src="screenshots/guide/login.png" width="700">

Once logged in, in their Keops **homepage**, Evaluators can see a list of **tasks** they are assigned to. They will also receive an email when they are assigned a task.
Evaluators just need to click the **start/continue** button of the desired task, and then start evaluating.

<img src="screenshots/tasks-page.png" width="700">

The __evaluation page__ will change depending on the type of the task which is being performed. KEOPS supports __Validation__, __Ranking__ and evaluation of __Adequacy__ and __Fluency__. For more information about this matter, please refer to the [Modes section in the Evaluators Guide](/evaluators.md#modes).

For example, Validation tasks are used to classify pairs of sentences using the [European Language Resource Coordination (ELRC)](http://www.lr-coordination.eu/) validation guidelines:

<img src="screenshots/evaluation-page.png" width="700">

Once all sentences of a task are evaluated, the Evaluator is redirected to the **recap** page for the task, where the evaluation stats of the task are shown. 
By marking the task as **DONE**, the Evaluator states that the task is finished, and from that moment, the Admin is able to **download** the stats of the evaluation and the evaluated sentences.

<img src="screenshots/guide/recap-val.png" width="700">


<a name="DB"></a>

## DB Schema ##

![DB Schema](keops.png)

A project can have several tasks (see table "tasks"), each one consisting of several sentences (see table "sentences_tasks"). 

Sentences belong to a corpus (see table "corpora" and "sentences"), but can be used in several  tasks (see table "sentences_tasks")

Sentences are uploaded individually (see table "sentences"), tagged as source or target and then related to each other (see table "sentences_pairing").

A task can only have one user assigned to it (see table "tasks"), but one user can have several tasks assignated.

An user (admin or evaluator) can have to several languages associated (see table "user_langs"). 

An admin can have several projects (see table "projects"), but each project has only one administrator (owner).

<a name="faq"></a>

## FAQ: Frequently Asked Questions ##

<a name="faq-corpora"></a>

### Which format must corpora have to be properly uploaded to Keops? ###

Corpora is always uploaded in TSV format. This format uses one line per record and a tab character to separate fields.

The specific format of the TSV file is explained below for each of the evaluation modes. This information is alsa available on KEOPS clicking on _First time uploading corpora_. You can also download a template and use it to upload your data:

* [Validation template](/corpora/templates/validation.tsv)
* [Adequacy template](/corpora/templates/adequacy.tsv)
* [Fluency template](/corpora/templates/fluency.tsv)
* [Ranking template](/corpora/templates/ranking.tsv)

#### Corpora for validation
| Source text | Tab | Target text |
|----------------------------------------------------------------------|-----|-------------------------------------------------------------------------------------------------------------|
| You can contact our customer service department using the form below | Tab | Puedes ponerte en contacto con nuestro departamento de servicio al cliente mediante el siguiente formulario |

You should only include one target text for each source text.

#### Corpora for adequacy
| Source text | Tab | Candidate translation |
|----------------------------------------------------------------------|-----|-------------------------------------------------------------------------------------------------------------|
| You can contact our customer service department using the form below | Tab | Puedes ponerte en contacto con nuestro departamento de servicio al cliente mediante el siguiente formulario |

You should only include one candidate translation for each source text.

#### Corpora for fluency
| Candidate translation |
|-----------------------|
Puedes ponerte en contacto con nuestro departamento de servicio al cliente mediante el siguiente formulario |

Corpora for fluency evaluation consist only on one column because they are monolingual tasks.

#### Corpora for ranking
| Source text | Tab | Reference text | Tab | Name of system 1 | Tab | Name of system 2 | Tab | ... |
|----------------------------------------------------------------------|-----|-------------------------------------------------------------------------------------------------------------|-----|--------------------------------|-----|--------------------------------|-----|-----|
| You can contact our customer service department using the form below | Tab | Puedes ponerte en contacto con nuestro departamento de servicio al cliente mediante el siguiente formulario | Tab | Manual de empleo y manutención | Tab | Manual de empleo y manutención | Tab | ... |

Include as many systems as you want.

<a name="faq-langs"></a>

### Which languages are preloaded in Keops? ###
 
Preloaded languages are:  Bulgarian (bg), Czech (cs), Danish (da),  German (de), Greek (el), English (en), Spanish (es), Estonian (et), Finnish (fi), French (fr),  
Irish (ga), Croatian (hr), Hungarian (hu), Italian (it), Lithuanian (lt), Latvian (lv), Maltese (mt), Norwegian - bokmal (no), Norwegian - nynorsk (nn), Dutch (nl), Polish (pl),  Portuguese (pt), Romanian (ro), 
Slovak (sk), Slovenian (sl) and Swedish (sv).

But remember: Admins can add as many languages as needed, at any time!

<a name="guidelines"></a>

### What are "validation guidelines"? ###

Evaluators working with Keops must follow the European Language Resource Coordination (ELRC) validation guidelines.

To ensure consistency from one evaluator to another, the following system has been adopted for grading translations.
Evaluators should use the following types/labels to tag problematic cases:

 
  * Wrong language identification 
  * Incorrect alignment
  * Wrong tokenization
  * MT translation
  * Translation error
  * Free translation
 

For more information on each label, please check the [ELRC Validation Guidelines document](http://www.lr-coordination.eu/sites/default/files/common/ELRC%20Data%20Validation%20Guidelines.pdf), section "4.2.2.2  Validation by human experts".

Remember: Evaluators can refer to the Validation Guidelines at any time, just clicking in the link in the Evaluation window.

<a name="summary-format"></a>

### Which format does the "task summary" file have? ###

The Task Summary (or task stats) file is a TSV file containing a summary of the task once it is finished. The format will change depending on the type of the task.

#### Validation
Each line contains the Label code, the Label description and the amount of entries tagged with that label in the task. For example:

```
Label 	Description           Count
L     	Wrong language id. 	  44
A     	Incorrect alignment   150
T     	Wrong tokenization    204
MT    	MT translation        97
E     	Translation error     70
F     	Free translation      39
V     	Valid translation     396
P     	Pending               0
Total 	Total                 1000
```
#### Adequacy
Each line contains a percentage (in steps of 10) and the amount of sentences evaluated with that score. For example:

```
Percentage 	# of sentences
0          	3
10         	2
20         	5
30         	1
40         	1
50         	0
60         	2
70         	4
80         	3
90         	4
100        	1
```

#### Fluency
Each line contains a percentage (in steps of 10) and the amount of sentences evaluated with that score. For example:

```
Percentage 	# of sentences
0          	0
10         	0
20         	1
30         	0
40         	0
50         	20
60         	0
70         	0
80         	0
90         	0
100        	0
```

#### Ranking
Each line contains the name of a system and its position in the ranking. For example:
```
System        Position
Google        7
Microsoft     1
DeepL         6
Apertium      3
PROMPT        3
```

<a name="annotation-format"></a>

### Which format does the "annotated sentences" file have? ###

The Annotated Sentences file is a TSV file containing all sentences that were evaluated in a task, as well as their evaluations and Evaluator comments (if any).

The format will change depending on the type of the task.

#### Validation

Each line contains the source and target texts, the source and target languages, the evaluation, description, evaluation details and the time it took the evaluator to evaluate the sentence. An example of an annotated sentences file follows:

| Source | Target | Source lang | Target lang | Evaluation | Description | Evaluation details | Time |
|----------------------------------------|----------------------------------|-------------|-------------|------------|-------------------|--------------------|------|
| Use and maintenance manual | Manual de empleo y mantenimiento | en | es | V | Valid translation |  | 28.5 |
| Tooling for cup chain aka tennis chain | Herramienta para cadena tennis | en | es | MT | MT translation |  | 30.1 |

#### Adequacy

Each line contains the source text, the target text, source and target languages, the score of the sentence, description, evaluation details and the time it took the evaluator to evaluate the sentence.

| Source | Target 1 | Source lang | Target lang | Evaluation | Description | Evaluation details | Time |
|-------------------------------------------------------------------------------------|--------------------------------------------------------------------------------------|-------------|-------------|------------|-------------|--------------------|------|
| If you did not find what wou were looking for, please use our custom search engine: | Si no encontró lo que está buscando, pruebe nuestro motor de búsqueda personalizada! | en | es | 20 |  |  | 20.1 |
| In this page, you will find information about Guided Tours of Dumbria. | En esta página encontrarás información sobre Visitas Guiadas de Dumbria. | en | es | 70 |  |  | 25.0 |

#### Fluency

Each line contains the target text, the target language, the score of the sentence, description, evaluation details and the time it took the evaluator to evaluate the sentence.

| Target | Target lang | Evaluation | Description | Evaluation details | Time |
|-------------------------------------------------------------------------------------|-------------|------------|-------------|--------------------|------|
| If you did not find what wou were looking for, please use our custom search engine: | en | 20 |  |  | 18.5 |
| In this page, you will find information about Guided Tours of Dumbria. | en | 70 |  |  | 21.2 |


#### Ranking
Each line contains a source text, a reference text, the candidate translations, the position of each one in JSON format, description, evaluation details and the time it took the evaluator to evaluate the sentence. For example:

| Source | Target | Google | Microsoft | DeepL | Apertium | PROMPT | Source lang | Target lang | Evaluation | Description | Evaluation details | Time |
|----------------------------|--------------------------------|--------------------------------|---------------------------------|--------------------------------|--------------------------------|---------------------------------|-------------|-------------|------------------------------------------------------------------------|-------------|--------------------|------|
| Use and maintenance manual | Manual de empleo y manutención | Manual de empleo y manutención | Manual de empleo y manutención. | Manual de empleo y manutención | Manual de empleo y manutención | Manual de empleo y manutención. | en | es | {"Google":"1","Microsoft":"2","DeepL":"3","Apertium":"4","PROMPT":"5"} |  |  | 55.8 |