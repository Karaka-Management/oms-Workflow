# Workflow Components

Every workflow has to provide a set of components in order to work. Other files are optional but can be used in order to enhance the experience. Additional files may be provided for better templating or for additional models but are not a necessety.

## Template

The `template.tpl.php` file contains the UI of the workflow.

## States

A `States.php` file contains all workflow states. This is especially important in order to show different content inside of the `template.tpl.php` depending on the state or in order to trigger state depended actions.

## Workflow

The `Workflow.php` file is the heart of every workflow. This file is responsible for executing state driven actions and it can also be seen as the API for a workflow. All workflow related actions will be forwarded to this file and can be handled inside including database queries.

##


1. Workflow gets installed with a trigger (either hook or cron job)
2. Trigger is fired
3. Hook loads CliApplication::installWorkflowTemplate with template ID, action ID and Hook
4. Installer creates WorkflowInstance Model
5. Installer calls CliApplication::runWorkflow with template, action ID, Hook, instance, and element for every element on this level.
6. runWorkflow executes the element by calling the respective function
7. runWorkflow calls itself with all child elements
