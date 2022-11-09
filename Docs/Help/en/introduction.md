# Introduction

The **Workflow** module allows organizations to automize and structure internal processes and guidelines. With workflows it's essentially possible to extend and modify how the application behaves based on the companies needs. Examples for workflows can be approval processes, step-by-step instructions which must be followed and many more.

## Target Group

The target group for this module is every organization which would like to create organization specific workflows/processes. The implementation of such workflows requires programming knowledge in PHP and potentially JavaScript. Other modules may provide their own example workflows which provide a good starting point and may be useful with minor configuration adjustments.

# Requirements

Workflows need to check many user actions and see if any workflows are linked to these user actions. This process is slow because of the large amount of potnetial user actions. In order to avoid the application slowing down it is recommended to run these checks asynchrounisly. However for this to be possible the application must have permissions to execute and run the cli application which is not possible on all server environments (e.g. simple web servers).

By default the application will test during the installation process if the Cli application can be executed from within the web application.

> Make sure you add the php.exe path to the environment variables on Windows!

# Setup

This module doesn't have any additional setup requirements since it is installed during the application install process.

# Features

## Permission Management

It's possible to only give selected users and groups access to certain workflows.

## Input handling

The workflows can be created in a way which allows UI interaction by the user, automatic modification through actions within the application and it's also possible to allow workflows to handle uploaded user data.

## Localization

The module allows users to create workflows which also support localization.
