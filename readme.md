Fortrabbit CLI (Un-official)
============================

Still under development - Do not use just yet!

## Usage

Looks for a .deploy folder in your project, that should contain environment config files for example, `production.yml`.

### Config File

Config files can be created when you first bootstrap your project - `frb init staging production` will create staging and production config files.

Alternatively, you can use the `frb make:env` command to add a sample file to your config directory.

The sample config file looks like this:

```yaml
name               : project_name
frb_zone           : deploy.eu2.frbit.com
target_branch      : origin/master
remote_branch      : master

build_command :
  - make
  - run

build_directory :
  - public/build
```

## To Do

- [ ] N init             : Bootstrap a project's .deploy folder
- [ ] N make:env         : Create an environment file
- [x] E ssh              : SSH into the server
- [x] E deploy           : Deploys to environment
- [x] E deploy:first     : Run the first deploy
- [x] E deploy:touch     : Deploys without uploading any assets
- [x] E deploy:assets    : Push assets only (--scp-only --build-only)
- [x] E remote:reset     : Reset the remote FRB instance
