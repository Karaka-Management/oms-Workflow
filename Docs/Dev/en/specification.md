# Specification

## Files

A workflow relies on some mandatory files which must be provided when creating a workflow. Additionally, workflows may provide optional files to enhance the user experience and provide additional functionality. The following names are reserved file names and must not be used for other purposes. Apart from these file names you may create and upload any template, helper or model files to create a workflow suitable to your needs.

### Controllers & Models (mandatory)

* WorkflowController.php
* WorkflowInstance.php
* WorkflowInstanceMapper.php

#### WorkflowController.php

The **WorkflowController.php** file must implement the **WorkflowControllerInterface** class. This file is mostly responsible to perform all actions associated with the workflow. Some of the functionality which would normally be implemented in this controller file are:

* Creating and updating workflow instances
* Performing workflow specific actions
* Displaying workflow information through templates (see below)
* Exporting and importing data from and to workflows

##### WorkflowControllerInterface

```php
interface WorkflowControllerInterface
{
    /**
     * Create instance from request
     *
     * @param RequestAbstract $request Request
     *
     * @return WorkflowInstanceAbstract
     *
     * @since 1.0.0
     */
    public function createInstanceFromRequest(RequestAbstract $request) : WorkflowInstanceAbstract;

    /**
     * Create list of all instances for this workflow from a request
     *
     * @param RequestAbstract $request Request
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getInstanceListFromRequest(RequestAbstract $request) : array;

    /**
     * Change workflow instance state
     *
     * @param RequestAbstract $request Request
     *
     * @return WorkflowInstanceAbstract
     *
     * @since 1.0.0
     */
    public function apiChangeState(RequestAbstract $request, ResponseAbstract $response, $data = null) : void;

    /**
     * Store instance model in the database
     *
     * @param WorkflowInstanceAbstract $instance Instance
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function createInstanceDbModel(WorkflowInstanceAbstract $instance) : void;
}
```

#### WorkflowInstance.php

The **WorkflowInstance.php** file hast to extend the **WorkflowInstanceAbstract.php** file. The workflow instance should contain most if not all of the information associated with a specific workflow instance. Since workflow data is highly dependent on the type of workflow every workflow must implement its own workflow instance model.

> Additional models may be useful depending on the workflow structure.

#### WorkflowInstanceMapper.php

The **WorkflowInstanceMapper.php** file has to extend the **DataMapperFactory.php** file. This file is responsible for reading, creating, updating and deleting a workflow instance in the database. If data needs to be stored in a database the workflow also must provide a **db.json** file (see below) which creates the database tables for the workflow.

> Additional mappers may be necessary if additional models are defined for the workflow.

### Routes & Hooks (mandatory)

* Hooks.php
* Routes.php

Both the hooks file and the routes file follow the same implementation guidelines as for modules. However the hooks and routes files for workflows will only be loaded when handling workflow actions.

### Database (optional but often necessary)

* db.json

The **db.json** file foollows the same implementation guidelines as for modules. This file is used to setup the database tables associated with the workflow (e.g. instance table).

Tables must follow the format `workflow_{workflow_id}_yourtablename` where `{workflow_id}` will be automatically replaced with the workflow id during the workflow setup process.

### Language file (optional but recommended)

* lang.php

Instead of using a fixed localization it is recommended to create a **lang.php** file which contains all the localizations. Even if only one localization is initially used this is the recommended way because it makes it very easy to extend to additional languages later on if necessary.

Format (example):

```php
<?php
declare(strict_types=1);

return [
    'en' => [
        'Identifier' => 'Translation in English',
    ],
    'de' => [
        'Identifier' => 'Translation in German',
	],
	'it' => [
        'Identifier' => 'Translation in Italian',
    ],
];

```

### Template files (optional but often recommended)

* instance-create.tpl.php
* instance-list.tpl.php
* instance-profile.tpl.php
* template-profile.tpl.php

#### instance-create.tpl.php

The **instance-create.tpl.php** is used to display a form which can be used to create a new workflow instance.

#### instance-list.tpl.php

The **instance-list.tpl.php** is used to show all instances of a workflow in a list.

#### instance-profile.tpl.php

The **instance-profile.tpl.php** is used to show the workflow instance with all its information (e.g. state, status, user data, ...). Additionally, this template may also contain user input options.

#### template-profile.tpl.php

The **template-profile.tpl.php** file may contain the workflow information for the admin, settings options for the workflow, file editor and more to manage the workflow.

### Export files (optional)

* Excel: *.xls.php / *.xlsx.php
* Word: *.doc.php / *.docx.php
* Powerpoint: *.ppt.php / *.pptx.php
* Pdf: *.pdf.php
* Json: *.json.php
* Csv: *.csv.php

The export files can be used to export workflow instance information in different layouts.