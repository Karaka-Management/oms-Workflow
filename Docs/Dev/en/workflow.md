
Every workflow can have up to 2 triggers (one existing trigger and one workflow specific trigger (trigger id))

Creating:

```mermaid
graph TD;
    CREATE_TEMPLATE((Create))-->REGISTER_TRIGGER[Register template trigger]
    REGISTER_TRIGGER-->HAS_OTHER_TRIGGER{Has other trigger}
    HAS_OTHER_TRIGGER--YES-->REGISTER_TRIGGER_2[Register existing trigger]
```

Running:

```mermaid
graph TD;
    MAIN_TRIGGER((Trigger))-->CREATE_INSTANCE[Create Instance in DB]
    MAIN_TRIGGER-->HAS_SUB_TRIGGERS{Has Sub Triggers}
    HAS_SUB_TRIGGERS--YES-->REGISTER_TRIGGER[Register sub triggers]
    MAIN_TRIGGER-->RUN_CODE_1[Run Code]
    RUN_CODE_1-->FORWARD_1[Run Code]
    FORWARD_1-->CONDITION{Condition}
    CONDITION-->RUN_CODE_3[Run Code]
```

How do workflow elements transfer data from one action to the next?
1. Through request/response objects?
   1. Action 1 creates response
   2. Workflow takes result and forms new request (expands original request)
   3. Action 2 takes request and performs action

This means that after every action a general workflow function has to take over and generate the next request. However this is to be expected anyways?

// Sample Workflows

Billing:

1. Get active subscriptions for the day
   1. option1: date (default = now)
   2. option2: client (default = wildcard for all)
   3. option3: payment type (default = cc, future = multiselect)
2. Create new invoice based on subscription
3. Is successful
   1. yes: send email
   2. no: inform sales person + deactivate benefits


