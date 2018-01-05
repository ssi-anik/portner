## Portner
Portner is an application that lets you keep track of the ports you are going to assign to a docker container.
It is not always possible to remember on which port the application is running. You may try to reassign the port to another application.
Here portner may help you in that case. If you start an application you can get the suggestion of ports you want to assign to your services. Later you can search with it.

### Installation
To install the portner application open your terminal
1. Download portner: `wget https://ssi-anik.github.io/portner/downloads/portner.phar`.
2. Check if the md5sum is same or not `e3b628f1103a245d8f048ccda68e66a6`.
2. Give the file executable permission: `sudo chmod +x portner.phar`.
3. Move the file to `/usr/local/bin` directory so that it can be accessed globally. `sudo mv portner.phar /usr/local/bin/portner`.

### Service
Services are the docker service names like `nginx`, `php`, `mysql`
- Add service:
  Add new service with the `portner service:add` or `portner sa` command
  - `--name` is required.
  - `--port` is required.
  - `--start-expose-at` optional if the port value is > 1024.
  Example: `portner service:add --name=elasticsearch --port=9200 --start-expose-at=9201`
- List services:
  To list the available services use `portner service:list` or `portner sl`
```
+---------------+-------------+---------------------+----------------+
| Service Name  | Actual Port | Host port expose at | Last used port |
+---------------+-------------+---------------------+----------------+
| nginx         | 80          | 8000                |                |
+---------------+-------------+---------------------+----------------+
| apache        | 80          | 9000                |                |
+---------------+-------------+---------------------+----------------+
| mysql         | 3306        | 3306                |                |
+---------------+-------------+---------------------+----------------+
| postgres      | 5432        | 5432                |                |
+---------------+-------------+---------------------+----------------+
| elasticsearch | 9200        | 9201                |                |
+---------------+-------------+---------------------+----------------+
```
- Remove service:
  To remove a service, you can use `portner service:remove` or `portner sr`
  - `--name` is optional. If omitted then it will show the services you want to remove. Answer with comma seperated index number.

### Application
Applications are the web application or any other application you will be creating. Like, `blog`, `e-commerce` applications.
- Add application:
  To add an application you'll have to use `portner appliation:add` or `portner aa` command.
  - The `--name` is a must.
  - `--services` is an optional. You can provide multiple comma separated list of available services. If omitted then it will prompt you a question to choose from the available services. It will then suggest you some ports. If you want to overwrite the suggested port just write it. And finally save it.
- Application list
  To view the list of application you can use `portner application:list` or `portner al`
```
+-------------------+---------------+------------+
| Application name  | Services      | Ports used |
+-------------------+---------------+------------+
| laravel-blog      | nginx         | 8000       |
|                   | mysql         | 3306       |
|                   | elasticsearch | 9201       |
+-------------------+---------------+------------+
| laravel-ecommerce | apache        | 9000       |
|                   | postgres      | 5432       |
|                   | elasticsearch | 9202       |
+-------------------+---------------+------------+
```
- Search application
  To search an application, you can use `portner application:search` or `portner as`
  - `--name`, to search in name.
  - `--service`, to search in services used.
  - `--port`, to search any specific port.
```
➜ portner as --port=5432
+-------------------+---------------+-------+
| Name              | Services      | Ports |
+-------------------+---------------+-------+
| laravel-ecommerce | apache        | 9000  |
|                   | postgres      | 5432  |
|                   | elasticsearch | 9202  |
+-------------------+---------------+-------+

➜ portner as --service=postgres
+-------------------+---------------+-------+
| Name              | Services      | Ports |
+-------------------+---------------+-------+
| laravel-ecommerce | apache        | 9000  |
|                   | postgres      | 5432  |
|                   | elasticsearch | 9202  |
+-------------------+---------------+-------+
```
- Remove application
  To remove an application, just type in your terminal: `portner application:remove` or `portner ar`
  - `--name` is an optional. If omitted it will show the available application list to select by index. In both cases you can supply multiple names.