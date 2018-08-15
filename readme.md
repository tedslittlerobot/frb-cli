Fortrabbit CLI (Un-official)
============================

Still under development

## Usage

Looks for a .deploy folder in your project, that should contain environment config files for example, `production.yml`:

## To Do

- N init             : Bootstrap a project's .deploy folder
- N make:env         : Create an environment file
- E ssh              : SSH into the server
- E deploy           : Deploys to environment
- E deploy:first     : Run the first deploy
- E deploy:touch     : Deploys without uploading any assets
- E deploy:assets    : Push assets only (--scp-only --build-only)
- E remote:reset     : Reset the remote FRB instance
