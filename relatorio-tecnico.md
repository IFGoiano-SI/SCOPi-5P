# Relatório Técnico — Infraestrutura SCOPi
**Disciplina:** Serviços de Redes de Computadores / Programação Web II  
**Projeto:** SCOPi — Sistema de Compras e Orçamentos de Produtos Inteligente  
**Autores:** Matheus Vieira da Silva, Elenilton Filho Nunes da Silva, Giovana Lyssa Galdino Ribeiro  

---

## 1. Introdução

Este relatório descreve a estruturação e a implementação da infraestrutura do sistema **SCOPi**, utilizando a tecnologia de containers **Docker** gerenciada através do **Docker Compose**. A arquitetura foi desenhada para garantir isolamento, portabilidade, segurança e escalabilidade dos serviços necessários para a execução da aplicação PHP/MySQL, integrando um servidor proxy reverso e um serviço de arquivos de rede (NFS).

---

## 2. Topologia de Rede e Arquitetura de Containers

A infraestrutura é composta por 4 serviços distintos integrados em uma rede virtual isolada denominada `app_net`. Essa topologia impede que containers de banco de dados ou de aplicação fiquem expostos diretamente à rede externa, centralizando todo o tráfego de entrada no proxy reverso Nginx.

### Diagrama Lógico de Comunicação

```mermaid
graph TD
    Client[Cliente / Navegador] -->|Porta 8050| Nginx[Container Nginx - Proxy Reverso]
    Client -->|Porta 8051| PMA[Container phpMyAdmin]
    
    subgraph Rede Interna Docker (app_net)
        Nginx -->|Porta 80| Apache[Container Apache + PHP 8.2]
        Apache -->|Porta 3306| MySQL[Container MySQL 8.0]
        PMA -->|Porta 3306| MySQL
        Apache <--->|Persistência de Uploads| Vol[Volume Nativo nfs_uploads]
    end

    classDef external fill:#f9f,stroke:#333,stroke-width:2px;
    classDef container fill:#bbf,stroke:#333,stroke-width:1px;
    class Client external;
    class Nginx,Apache,MySQL,PMA container;
```

---

## 3. Configuração do Servidor Web (Proxy Reverso Nginx)

O servidor web utiliza o **Nginx** como a camada frontal de Proxy Reverso, delegando o processamento do código da aplicação PHP ao container **Apache/PHP** (back-end).

### Benefícios dessa abordagem:
1. **Segurança por Ocultação:** O container do Apache (porta interna `80`) não expõe nenhuma porta para o host externo. Todo o tráfego HTTP é filtrado e inspecionado pelo Nginx.
2. **Encaminhamento de Cabeçalhos (Headers):** A diretiva de proxy está configurada para encaminhar o IP real do cliente (`X-Real-IP`, `X-Forwarded-For`) e os cabeçalhos de Host originais contendo a porta do cliente (`proxy_set_header Host $http_host`). Isso garante que o PHP consiga construir dinamicamente URLs absolutas usando a porta correta de comunicação (como a porta `8050` do host), além de viabilizar a auditoria de logs.
3. **Controle de Uploads:** Definição da diretiva `client_max_body_size 20M` no Nginx, permitindo o recebimento de arquivos de Nota Fiscal (XML) e outros anexos necessários na aplicação, evitando o bloqueio padrão de 1MB.

### Arquivo de Configuração (`nginx.conf`):
```nginx
events {
    worker_connections 1024;
}

http {
    include       /etc/nginx/mime.types;
    default_type  application/octet-stream;

    sendfile        on;
    keepalive_timeout  65;

    server {
        listen 80;
        server_name localhost;

        location / {
            proxy_pass http://apache:80;
            
            # Preserva o Host original com a porta (importante para o BASE_URL do PHP resolver com a porta 8050)
            proxy_set_header Host $http_host;
            
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto $scheme;
            client_max_body_size 20M;
        }

        error_page   500 502 503 504  /50x.html;
        location = /50x.html {
            root   /usr/share/nginx/html;
        }
    }
}
```

---

## 4. Estruturação do Serviço de Arquivos (NFS)

Para atender ao requisito de persistência de uploads e simular um armazenamento compartilhado em rede (NFS), a arquitetura utiliza um volume nomeado nativo do Docker (`nfs_uploads`).

### Detalhes de estruturação e compatibilidade:
1. **Volume Nativo do Docker (`nfs_uploads`):** Mapeado no container da aplicação Apache/PHP em `/var/www/html/public/uploads`.
2. **Abstração do Serviço NFS:** Esta montagem encapsula e simula o comportamento de um Network File System (NFS), permitindo que os arquivos de upload sejam persistidos fora do ciclo de vida dos containers.
3. **Compatibilidade Multiplataforma:** Ao utilizar o driver nativo (`local`) do Docker para gerenciar o volume, elimina-se a dependência de módulos de kernel específicos (como o módulo `nfs` ou `nfsd` do Linux) na máquina hospedeira. Isso garante que a infraestrutura rode de maneira estável em qualquer ambiente de desenvolvimento que utilize WSL2 ou Docker Desktop no Windows/macOS, onde esses módulos não vêm pré-instalados ou ativados por padrão, evitando loops de erro ou falhas no boot do container.

---

## 5. Integração com Banco de Dados (MySQL)

O banco de dados foi configurado utilizando a imagem oficial do **MySQL 8.0**:
* **Banco Padrão:** Definido como `scopi` no ambiente Docker e mapeado no arquivo `.env` da aplicação.
* **Auto-inicialização de Dados:** O arquivo dump do banco de dados `./SCOPi.sql` foi mapeado para `/docker-entrypoint-initdb.d/init.sql` no container do MySQL. Isso garante que, na primeira inicialização do ambiente, as tabelas (departamentos, usuários, cotações) e o usuário administrador padrão (`admin@scopi.com` / `password`) sejam criados automaticamente, deixando o sistema 100% pronto para uso.

---

## 6. Instruções de Execução do Ambiente

Para iniciar toda a infraestrutura de containers descrita e testar o sistema localmente, execute o seguinte comando no terminal (na raiz do projeto):

```bash
docker-compose up -d
```

### URLs de Acesso Local:
* **Sistema SCOPi (via Nginx):** [http://localhost:8050](http://localhost:8050)
* **phpMyAdmin (Administração BD):** [http://localhost:8051](http://localhost:8051)
* **Credenciais de Teste:**
  * **E-mail:** `admin@scopi.com`
  * **Senha:** `password`
