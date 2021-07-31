# Specification

```json
{
	"steps": [
		{
			"name": "step-name",
			"event-name": "unique-event-name-generated-on-upload",
			"type": 1,
			"template": "visual-template",
			"users": [],
			"groups": [],
			"event-listeners": [
				"event-to-listen-for-1"
				"event-to-listen-for-2"
			],
			"pre": {
				"event-trigger": [
					"event-to-trigger-1"
					"event-to-trigger-2"
				],
				"script": [
					"script-to-run-1"
					"script-to-run-2"
				]
			},
			"post": {
				"event-trigger": [
					"event-to-trigger-1"
					"event-to-trigger-2"
				],
				"script": [
					"script-to-run-1"
					"script-to-run-2"
				]
			}
		},
		{
			....
		}
	]
}
```

Good synergy with job module required... job runs every x and might invoke a workflow/workflow-step

## Type

* 1 = Autorun after previous event
* 2 = Only if event listener is called

Every step receives the status code of all steps + the custom data created from the previous steps

After every step the handler writes custom data to the workflow run information.

This means every workflow has a configuration and whenever a new workflow get's triggered a new "workflow entry" is created.

## Template

e.g. user interface for this step