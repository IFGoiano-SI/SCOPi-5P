**INSTITUTO FEDERAL GOIANO \- CAMPUS CERES**  
**BACHARELADO EM SISTEMAS DE INFORMAÇÃO**  
**5º PERÍODO DE SISTEMAS DE INFORMAÇÃO**  
**Elenilton Filho Nunes da Silva**   
**Giovana Lyssa Galdino Ribeiro**  
**Matheus Vieira da Silva**

**SCOPi – Sistema de Compras e Orçamentos de Produtos Inteligente** 

**CERES**  
**2026**  
**Elenilton Filho Nunes da Silva**   
**Giovana Lyssa Galdino Ribeiro**  
**Matheus Vieira da Silva**

**SCOPi – Sistema de Compras e Orçamentos de Produtos Inteligente** 

Trabalho integrador apresentado às disciplinas de Gerência de Projetos de Software, Programação Web II e Serviços de Redes de Computadores, do curso de Bacharelado em Sistemas de Informação do Instituto Federal Goiano \- Campus Ceres, como requisito parcial de avaliação semestral, sob orientação dos professores Paulo Henrique Araujo, Vilson Soares de Siqueira e Roitier Campos Gonçalves.

**CERES**  
**2026**  
**LISTA DE ILUSTRAÇÕES**

**Imagem 1 \- Diagrama de Entidade e Relacionamentos	14**

**LISTA DE TABELAS**

**Tabela 01 \- Quadro Resumo de Requisitos**	

**Tabela 02 \- Módulo de Login para Funcionários**	

**Tabela 03 \- Menu de Navegação**	

**Tabela 04 \- Módulo de Cadastro e Gerenciamento de Usuários**	

**Tabela 05 \- Módulo de Cadastro e Gerenciamento de Departamentos**	

**Tabela 06 \- Módulo de Cadastro e Gerenciamento de Fornecedores**	

**Tabela 07 \- Módulo de Cadastro e Gerenciamento de Produtos**	

**Tabela 08 \- Módulo de Criação e Gerenciamento de Solicitações de Produto**	

**Tabela 09 \- Módulo de Criação e Gerenciamento de Cotações**	

**Tabela 10 \- Módulo de Login de Fornecedor**	

**Tabela 11 \- Módulo de Preenchimento de Cotações para Fornecedores**	

**Tabela 12 \- Módulo de Criação e Gerenciamento de Ordens de Compras**	

**Tabela 13 \- Módulo de Gerenciamento de Notas Fiscais de Terceiros**	

**Tabela 14 \- Segurança e Controle de Acesso**	

**Tabela 15 \- Proteção de Dados e Conformidade com a LGPD**	

**Tabela 16 \- Desempenho e Tempo de Resposta**	

**Tabela 17 \- Disponibilidade e Confiabilidade do Sistema**	

**Tabela 18 \- Auditoria e Rastreabilidade de Operações**	

**Tabela 19 \- Integridade e Consistência dos Dados**	

**Tabela 20 \- Backup e Recuperação de Dados**	

**Tabela 21 \- Responsividade e Compatibilidade da Interface**	

**Tabela 22 \- Banco de Dados Escalável e Otimizado**	

**colocar número das páginas**

**SUMÁRIO**

**[1\. APRESENTAÇÃO DO SOFTWARE	4](#apresentação-do-software)**

[1.1. PROBLEMA E JUSTIFICATIVA	4](#1.1.-problema-e-justificativa)

[1.2. OBJETIVO	4](#1.2.-objetivo)

[1.3. ESCOPO DO PRODUTO	4](#1.3.-escopo-do-produto)

[**2\. ESPECIFICAÇÃO DE REQUISITOS	5**](#especificação-de-requisitos)

[2.1. QUADRO RESUMO DE REQUISITOS	5](#2.1.-quadro-resumo-de-requisitos)

[2.2. REQUISITOS FUNCIONAIS	6](#2.2.-requisitos-funcionais)

[2.3. REQUISITOS NÃO FUNCIONAIS	14](#2.3.-requisitos-não-funcionais)

[**3\. GERÊNCIA DO PROJETO	17**](#gerência-do-projeto)

[3.1. PLANEJAMENTO	18](#3.1.-planejamento)

[3.2. ORGANIZAÇÃO E RECURSOS	18](#3.2.-organização-e-recursos)

[3.3. EXECUÇÃO E MONITORAMENTO	19](#3.3.-execução-e-monitoramento)

[**4\. EAP \- ESTRUTURA ANALÍTICA DO PROJETO	19**](#eap---estrutura-analítica-do-projeto)

[4.1 QUADRO EAP	19](#4.1-quadro-eap)

[4.2. DICIONÁRIO DA EAP	20](#4.2.-dicionário-da-eap)

[**5\. CRONOGRAMA	21**](#cronograma)

[**6\. PLANO DE RISCOS	22**](#plano-de-riscos)

[6.1. MATRIZ DE RISCO QUANTITATIVA	22](#6.1.-matriz-de-risco-quantitativa)

[6.2. IDENTIFICAÇÃO E ANÁLISE DOS RISCOS	22](#6.2.-identificação-e-análise-dos-riscos)

[6.3. PLANO DE AÇÃO	28](#6.3.-plano-de-ação)

[**7\. MODELO CANVAS \- Fazer	31**](#modelo-canvas---fazer)

[**8\. DER  \- DIAGRAMA DE ENTIDADE E RELACIONAMENTOS \- Fazer	31**](#der---diagrama-de-entidade-e-relacionamentos---fazer)

[**9\. TOPOLOGIA DE REDE E ARQUITETURA DE CONTAINERS	31**](#topologia-de-rede-e-arquitetura-de-containers)

[9.1. TOPOLOGIA DE REDE E ARQUITETURA DE CONTAINERS	31](#9.1.-topologia-de-rede-e-arquitetura-de-containers)

[**10\. DESENVOLVIMENTO E INFRAESTRUTURA	31**](#desenvolvimento-e-infraestrutura)

[10.1. ARQUITETURA DO SOFTWARE	31](#10.1.-arquitetura-do-software)

[10.2. BACK-END	32](#10.2.-back-end)

[10.3. INFRAESTRUTURA E DEPLOY	32](#10.3.-infraestrutura-e-deploy)

[10.4. INTEGRAÇÃO E TESTES	32](#10.4.-integração-e-testes)

[**11\. ENCERRAMENTO	32**](#encerramento)

[**REFERÊNCIAS	33**](#referências)

1. # **APRESENTAÇÃO DO SOFTWARE** {#apresentação-do-software}

	O SCOPi \- Sistema de Compras e Orçamentos de Produtos Inteligente \- é uma aplicação web desenvolvida para centralizar e automatizar o processo de compras corporativas. O nome foi escolhido de forma a refletir as principais funcionalidades do sistema, uma vez que o sistema foi pensado para atender empresas que ainda realizam suas aquisições de forma manual, por meio de planilhas, trocas de e-mails e registros não padronizados. 

## **1.1. PROBLEMA E JUSTIFICATIVA** {#1.1.-problema-e-justificativa}

	A abordagem manual no processo de compras pode acarretar atrasos, retrabalho, compras duplicadas, perda de informações e falta de controle sobre os custos, impactando diretamente a eficiência operacional e dificultando a tomada de decisão nas organizações. Como aponta Qualhato (2023), "anteriormente, as negociações eram realizadas por meio de comunicação via e-mail e exigiam a inserção manual dos itens no sistema da empresa" (p. 13). A ausência de padronização e a ineficiência nos processos reforçam a necessidade de soluções tecnológicas otimizadas, voltadas à modernização da área de compras. 

## **1.2. OBJETIVO** {#1.2.-objetivo}

O SCOPi por sua vez tem como objetivo digitalizar e centralizar o ciclo de compras corporativas, eliminando a dependência de processos manuais e oferecendo um ambiente integrado para solicitação, cotação, aprovação e registro das aquisições. Diferente de outros softwares semelhantes, o sistema integra recursos como rastreamento do fluxo do sistema e interação direta do fornecedor com o sistema por meio de links individuais e temporários. Esses recursos visam não apenas garantir transparência e rastreabilidade, mas também simplificar a comunicação e reduzir etapas manuais, tornando o processo de compras mais ágil e seguro. 

## **1.3. ESCOPO DO PRODUTO** {#1.3.-escopo-do-produto}

	O SCOPi automatiza o ciclo completo de compras corporativas, desde a solicitação de produtos até o registro final das aquisições. O sistema integra login seguro com controle de acesso por perfil e módulos de cadastro de usuários, departamentos, fornecedores e produtos, com histórico de alterações em todas as operações.  
Os setores registram solicitações de produtos, que seguem ao comprador somente após autorização do gerente responsável. O comprador pode então abrir um processo de cotação, enviando links individuais e temporários aos fornecedores selecionados, que preenchem suas propostas dentro do prazo estabelecido. O sistema exibe uma tela comparativa das respostas, permitindo que o comprador selecione a proposta vencedora e gere a ordem de compra correspondente.  
A ordem de compra depende de autorização do gerente de compras para envio ao fornecedor. O setor contábil registra e importa as notas fiscais recebidas, vinculando-as às ordens de compra e encerrando o ciclo automaticamente quando todos os itens estiverem atendidos.

2. # **ESPECIFICAÇÃO DE REQUISITOS** {#especificação-de-requisitos}

## **2.1. QUADRO RESUMO DE REQUISITOS** {#2.1.-quadro-resumo-de-requisitos}

| QUADRO RESUMO DE REQUISITOS |  |  |  |
| ----- | :---- | ----- | :---- |
| FUNCIONAIS |  | NÃO FUNCIONAIS |  |
| RF01 | Módulo de Login para Funcionários | RNF01 | Segurança e Controle de Acesso |
| RF02 | Menu de Navegação | RNF02 | Proteção de Dados e Conformidade com a LGPD |
| RF03 | Módulo de Cadastro e Gerenciamento de Usuários | RNF03 | Desempenho e Tempo de Resposta |
| RF04 | Módulo de Cadastro e Gerenciamento de Departamentos | RNF04 | Disponibilidade e Confiabilidade do Sistema |
| RF05 | Módulo de Cadastro e Gerenciamento de Fornecedores | RNF05 | Auditoria e Rastreabilidade de Operações |
| RF06 | Módulo de Cadastro e Gerenciamento de Produtos | RNF06 | Integridade e Consistência dos Dados |
| RF07 | Módulo de Criação e Gerenciamento de Solicitações de Produto | RNF07 | Backup e Recuperação de Dados |
| RF08 | Módulo de Criação e Gerenciamento de Cotações | RNF08 | Responsividade e Compatibilidade da Interface |
| RF09 | Módulo de Login de Fornecedor | RNF09 | Banco de Dados Escalável e Otimizado |
| RF10 | Módulo de Preenchimento de Cotações para Fornecedores |  |  |
| RF11 | Módulo de Criação e Gerenciamento de Ordens de Compras |  |  |
| RF12 | Módulo de Gerenciamento de Notas Fiscais de Terceiros |  |  |

Tabela 01 \- Quadro Resumo de Requisitos

## **2.2. REQUISITOS FUNCIONAIS** {#2.2.-requisitos-funcionais}

| REQUISITO FUNCIONAL 01 |  |  |  |  |  |  |
| ----- | :---- | ----- | :---- | :---- | ----- | ----- |
| Identificação: | RF01 |  |  |  |  |  |
| Descrição: | Módulo de Login para Funcionários |  |  |  |  |  |
| Responsável: | Elenilton |  |  | Local: |  |  |
| Fonte do Requisito: | Giovana |  |  | Data: | 14/05/2026 |  |
| Prioridade: | Alta |  |  | Dificuldade: | Fácil |  |
| Especificação |  |  |  |  |  |  |
| O sistema deve permitir que funcionários acessem a aplicação por meio de autenticação utilizando e-mail e senha. O sistema deve validar as credenciais informadas e permitir o acesso apenas a usuários ativos e devidamente cadastrados. Caso o usuário não se recorde de sua senha, o sistema deve disponibilizar uma funcionalidade de recuperação de acesso. Após a autenticação, o sistema deve direcionar o usuário para a área inicial da aplicação, exibindo apenas os módulos e funcionalidades permitidos conforme seu perfil de acesso. A tela de login deve apresentar os campos de e-mail e senha de forma clara, com botão de acesso em destaque e identidade visual compatível com o sistema. O sistema deve bloquear temporariamente o acesso após sucessivas tentativas de autenticação inválidas. |  |  |  |  |  |  |

Tabela 02 \- Módulo de Login para Funcionários

| REQUISITO FUNCIONAL 02 |  |  |  |  |  |  |
| ----- | :---- | ----- | :---- | :---- | ----- | ----- |
| Identificação: | RF02 |  |  |  |  |  |
| Descrição: | Menu de Navegação |  |  |  |  |  |
| Responsável: | Elenilton |  |  | Local: |  |  |
| Fonte do Requisito: | Elenilton |  |  | Data: | 14/05/2026 |  |
| Prioridade: | Alta |  |  | Dificuldade: | Fácil |  |
| Especificação |  |  |  |  |  |  |
| O sistema deve possuir um menu de navegação lateral estruturado de forma hierárquica, permitindo acesso organizado aos módulos e funcionalidades disponíveis na aplicação. O menu deve exibir apenas os módulos, páginas e funcionalidades permitidos ao usuário autenticado, conforme seu perfil de acesso e permissões vinculadas. O sistema deve permitir a expansão e recolhimento das categorias do menu, facilitando a navegação entre os módulos do sistema. A navegação deve manter consistência visual e estrutural entre as telas da plataforma, permitindo que o usuário identifique facilmente sua localização dentro do sistema. O sistema deve destacar visualmente o módulo ou página atualmente acessada pelo usuário. |  |  |  |  |  |  |

Tabela 03 \- Menu de Navegação

| REQUISITO FUNCIONAL 03 |  |  |  |  |  |  |
| ----- | :---- | ----- | :---- | :---- | ----- | ----- |
| Identificação: | RF03 |  |  |  |  |  |
| Descrição: | Módulo de Cadastro e Gerenciamento de Usuários |  |  |  |  |  |
| Responsável: | Elenilton |  |  | Local: |  |  |
| Fonte do Requisito: | Matheus |  |  | Data: | 14/05/2026 |  |
| Prioridade: | Alta |  |  | Dificuldade: | Intermediário |  |
| Especificação |  |  |  |  |  |  |
| O sistema deve permitir que usuários com perfil de administrador realizem o cadastro e gerenciamento de usuários vinculados a funcionários da empresa. O cadastro de usuário deve conter, no mínimo, nome, e-mail, departamento, matrícula, número para contato, perfil de acesso e status. O status do usuário deve ser definido como ativo por padrão. O sistema deve permitir que usuários definam ou redefinam suas senhas de acesso por meio de mecanismos seguros de recuperação de credenciais, respeitando regras mínimas de complexidade, como comprimento mínimo e uso de caracteres variados. O perfil de acesso deve determinar quais módulos, funcionalidades e operações estarão disponíveis para o usuário, podendo ser atualizado conforme mudança de função, promoção ou necessidade administrativa. O sistema deve permitir que administradores editem, inativem e reativem usuários cadastrados, preservando o histórico de ações realizadas pelo usuário mesmo em casos de desligamento da empresa. O sistema deve notificar o usuário sempre que houver alterações relevantes em seu cadastro, status ou perfil de acesso. O sistema deve permitir a listagem, pesquisa e geração de relatórios de usuários cadastrados, respeitando as permissões de visualização definidas pelo perfil de acesso. Usuários administradores devem possuir acesso à visualização de todos os usuários cadastrados, enquanto gerentes de departamento devem visualizar apenas usuários vinculados ao seu respectivo departamento. Todas as alterações realizadas no cadastro de usuários devem ser registradas em histórico, armazenando os dados alterados, os novos valores, data da alteração e identificação do usuário responsável pela operação. |  |  |  |  |  |  |

Tabela 04 \- Módulo de Cadastro e Gerenciamento de Usuários

| REQUISITO FUNCIONAL 04 |  |  |  |  |  |  |
| ----- | :---- | ----- | :---- | :---- | ----- | ----- |
| Identificação: | RF04 |  |  |  |  |  |
| Descrição: | Módulo de Cadastro e Gerenciamento de Departamentos |  |  |  |  |  |
| Responsável: | Elenilton |  |  | Local: |  |  |
| Fonte do Requisito: | Matheus |  |  | Data: | 14/05/2026 |  |
| Prioridade: | Média |  |  | Dificuldade: | Intermediário |  |
| Especificação |  |  |  |  |  |  |
| O sistema deve permitir que usuários com perfil de cadastrador realizem o cadastro e gerenciamento de departamentos da empresa. O cadastro de departamento deve conter, no mínimo, nome, código identificador gerado automaticamente pelo sistema, gerente responsável e status. O status do departamento deve ser definido como ativo por padrão. O sistema deve permitir que usuários com perfil de cadastrador editem, inativem e reativem departamentos cadastrados. Cada departamento deve possuir um gerente responsável vinculado, o qual será utilizado para controle das operações e visualização das informações relacionadas ao departamento. O sistema deve notificar o gerente responsável sempre que houver alterações relevantes no cadastro do departamento. O sistema deve permitir a listagem, pesquisa e geração de relatórios de departamentos cadastrados, respeitando as permissões definidas pelo perfil de acesso. Usuários com perfil de administrador ou cadastrador devem possuir acesso à visualização de todos os departamentos cadastrados, enquanto gerentes devem possuir acesso apenas às informações relacionadas ao seu respectivo departamento. Todas as alterações realizadas no cadastro de departamentos devem ser registradas em histórico, armazenando os dados alterados, os novos valores, data da alteração e identificação do usuário responsável pela operação. |  |  |  |  |  |  |

Tabela 05 \- Módulo de Cadastro e Gerenciamento de Departamentos

| REQUISITO FUNCIONAL 05 |  |  |  |  |  |  |
| ----- | :---- | ----- | :---- | :---- | ----- | ----- |
| Identificação: | RF05 |  |  |  |  |  |
| Descrição: | Módulo de Cadastro e Gerenciamento de Fornecedores |  |  |  |  |  |
| Responsável: | Elenilton |  |  | Local: |  |  |
| Fonte do Requisito: | Matheus |  |  | Data: | 14/05/2026 |  |
| Prioridade: | Alta |  |  | Dificuldade: | Intermediário |  |
| Especificação |  |  |  |  |  |  |
| O sistema deve permitir que usuários com perfil de cadastrador realizem o cadastro e gerenciamento de fornecedores vinculados ao processo de compras da empresa. O cadastro de fornecedor deve conter, no mínimo, razão social, nome fantasia, CNPJ, inscrição estadual, endereço, e-mail para contato, número para contato, nome do responsável pelo atendimento, categoria, código identificador gerado automaticamente pelo sistema, vínculo com matriz ou filial, quando aplicável, e status. O status do fornecedor deve ser definido como ativo por padrão. O sistema deve permitir que usuários com perfil de cadastrador editem, inativem e reativem fornecedores cadastrados. O sistema deve permitir o vínculo de fornecedores aos produtos e categorias de produtos que poderão ser fornecidos pela empresa cadastrada. O sistema deve permitir que fornecedores recebam links temporários e individuais de acesso às cotações vinculadas ao seu cadastro. O sistema deve permitir que usuários vinculados aos setores de compras, administração e departamentos solicitantes tenham acesso à listagem e pesquisa de fornecedores cadastrados, podendo também ser gerados relatórios a partir dos resultados das listagens e pesquisas. Todas as alterações realizadas no cadastro de fornecedores devem ser registradas em histórico, armazenando os dados alterados, os novos valores, data da alteração e identificação do usuário responsável pela operação. |  |  |  |  |  |  |

Tabela 06 \- Módulo de Cadastro e Gerenciamento de Fornecedores

| REQUISITO FUNCIONAL 06 |  |  |  |  |  |  |
| ----- | :---- | ----- | :---- | :---- | ----- | ----- |
| Identificação: | RF06 |  |  |  |  |  |
| Descrição: | Módulo de Cadastro e Gerenciamento de Produtos |  |  |  |  |  |
| Responsável: | Elenilton |  |  | Local: |  |  |
| Fonte do Requisito: | Matheus |  |  | Data: | 14/05/2026 |  |
| Prioridade: | Alta |  |  | Dificuldade: | Intermediário |  |
| Especificação |  |  |  |  |  |  |
| O sistema deve permitir que usuários com perfil de cadastrador realizem o cadastro e gerenciamento de produtos utilizados nos processos de solicitação, cotação e compra da empresa. O cadastro de produto deve conter, no mínimo, nome, descrição, código identificador gerado automaticamente pelo sistema, categoria e status. O status do produto deve ser definido como ativo por padrão. O sistema deve permitir que usuários com perfil de cadastrador editem, inativem e reativem produtos cadastrados. Os produtos devem poder ser vinculados aos fornecedores que comercializam esse tipo de mercadoria. O sistema deve permitir que qualquer funcionário tenha acesso à listagem e pesquisa de produtos cadastrados, podendo também ser gerados relatórios a partir dos resultados das listagens e pesquisas. Todas as alterações realizadas no cadastro de produtos devem ser registradas em histórico, armazenando os dados alterados, os novos valores, data da alteração e identificação do usuário responsável pela operação. |  |  |  |  |  |  |

Tabela 07 \- Módulo de Cadastro e Gerenciamento de Produtos

| REQUISITO FUNCIONAL 07 |  |  |  |  |  |  |
| ----- | :---- | ----- | :---- | :---- | ----- | ----- |
| Identificação: | RF07 |  |  |  |  |  |
| Descrição: | Módulo de Criação e Gerenciamento de Solicitações de Produto |  |  |  |  |  |
| Responsável: | Elenilton |  |  | Local: |  |  |
| Fonte do Requisito: | Matheus |  |  | Data: | 14/05/2026 |  |
| Prioridade: | Alta |  |  | Dificuldade: | Intermediário |  |
| Especificação |  |  |  |  |  |  |
| O sistema deve permitir que usuários vinculados a funcionários da empresa realizem solicitações de produtos cadastrados e ativos na plataforma. A solicitação deve conter, no mínimo, número identificador, um ou mais produtos, quantidade solicitada, departamento solicitante, justificativa, usuário solicitante e status. O departamento solicitante deve ser vinculado automaticamente ao departamento do usuário responsável pela solicitação. O status da solicitação deve permitir, no mínimo, os estados: em aberto, autorizada, em cotação, recusada, cancelada e concluída. O status em cotação deve ser atribuído automaticamente pelo sistema no momento em que o comprador iniciar o processo de cotação vinculado à solicitação. O sistema deve permitir que usuários vinculados ao departamento solicitante editem solicitações enquanto estiverem com status em aberto. Para que a solicitação siga para o processo de cotação e compra, o gerente responsável pelo departamento solicitante deve autorizar a solicitação, alterando seu status para autorizada. O sistema deve permitir que o gerente responsável retire a autorização de uma solicitação, desde que o processo de cotação ainda não tenha sido iniciado, permitindo novamente sua edição pelo departamento solicitante. O sistema deve permitir o cancelamento de solicitações em aberto pelos usuários vinculados ao departamento solicitante. Solicitações já autorizadas poderão ser canceladas apenas pelo gerente responsável do departamento e somente enquanto ainda não houver iniciado o processo de cotação. O sistema deve permitir a listagem, pesquisa e geração de relatórios de solicitações, respeitando as permissões definidas pelo perfil de acesso. Usuários comuns devem possuir acesso apenas às solicitações vinculadas ao seu departamento, enquanto usuários dos setores de compras e administradores poderão visualizar solicitações de todos os departamentos. Todas as alterações realizadas nas solicitações devem ser registradas em histórico, armazenando os dados alterados, os novos valores, data da alteração e identificação do usuário responsável pela operação. |  |  |  |  |  |  |

Tabela 08 \- Módulo de Criação e Gerenciamento de Solicitações de Produto

| REQUISITO FUNCIONAL 08 |  |  |  |  |  |  |
| ----- | :---- | ----- | :---- | :---- | ----- | ----- |
| Identificação: | RF08 |  |  |  |  |  |
| Descrição: | Módulo de Criação e Gerenciamento de Cotações |  |  |  |  |  |
| Responsável: | Elenilton |  |  | Local: |  |  |
| Fonte do Requisito: | Matheus |  |  | Data: | 14/05/2026 |  |
| Prioridade: | Alta |  |  | Dificuldade: | Intermediário |  |
| Especificação |  |  |  |  |  |  |
| O sistema deve permitir que usuários com perfil de comprador criem e gerenciem cotações a partir de solicitações de produtos autorizadas pelos gerentes responsáveis. Quando uma solicitação de produto for autorizada, o sistema deve permitir que o comprador defina se os itens seguirão para processo de cotação ou diretamente para geração da ordem de compra. Caso o comprador opte pelo processo de cotação, o sistema deve gerar automaticamente a estrutura inicial da cotação com base nos dados da solicitação autorizada. Ao iniciar uma cotação, o sistema deve permitir que o comprador selecione os fornecedores vinculados aos produtos da solicitação. O sistema deve gerar uma cotação contendo, no mínimo, data de início, data de encerramento, produtos cotados, quantidade solicitada, fornecedores participantes e status. O status da cotação deve permitir, no mínimo, os estados: aberta, fechada, concluída e cancelada. O sistema deve enviar aos fornecedores selecionados um link individual de acesso à cotação, contendo um token temporário e único vinculado ao fornecedor e à cotação, válido apenas enquanto a cotação estiver aberta. O sistema deve registrar quais fornecedores receberam o link de acesso e quais enviaram resposta à cotação. O sistema deve permitir que o comprador regenere ou reenvie o link de acesso para um ou mais fornecedores vinculados à cotação, invalidando automaticamente os tokens anteriores quando necessário. Ao receber as respostas dos fornecedores, o sistema deve exibir uma tela de análise comparativa das propostas, apresentado lado a lado as informações como valor unitário, impostos, taxas adicionais, modalidade do frete, transportadora, prazo de entrega, condição de pagamento, validade da proposta, garantia e observações. O sistema deve permitir que o comprador selecione a proposta vencedora, registrando-a como resposta escolhida e permitindo a geração da ordem de compra correspondente. O sistema deve permitir apenas uma proposta vencedora por cotação. O sistema deve permitir que o comprador solicite renegociação de uma proposta enquanto a cotação estiver aberta, possibilitando que o fornecedor atualize sua resposta pelo mesmo link de acesso. O sistema deve permitir a listagem, pesquisa e geração de relatórios de cotações e respostas, respeitando as permissões definidas pelo perfil de acesso. Todas as alterações realizadas em cotações e respostas de fornecedores devem ser registradas em histórico, armazenando os dados alterados, os novos valores, data da alteração e identificação do responsável pela operação. |  |  |  |  |  |  |

Tabela 09 \- Módulo de Criação e Gerenciamento de Cotações

| REQUISITO FUNCIONAL 09 |  |  |  |  |  |  |
| ----- | :---- | ----- | :---- | :---- | ----- | ----- |
| Identificação: | RF09 |  |  |  |  |  |
| Descrição: | Módulo de Login de Fornecedor |  |  |  |  |  |
| Responsável: | Elenilton |  |  | Local: |  |  |
| Fonte do Requisito: | Matheus |  |  | Data: | 14/05/2026 |  |
| Prioridade: | Alta |  |  | Dificuldade: | Intermediário |  |
| Especificação |  |  |  |  |  |  |
| O sistema deve permitir que fornecedores acessem a plataforma por meio de links individuais contendo tokens temporários de autenticação vinculados à cotação correspondente. Os links de acesso devem ser enviados automaticamente ao e-mail cadastrado do fornecedor no momento da abertura da cotação, permanecendo válidos apenas durante o período em que a cotação estiver aberta. Ao acessar o link recebido, o fornecedor deve ser redirecionado para uma tela de login exclusiva para fornecedores, seguindo o padrão visual da tela de login principal do sistema. A tela de login do fornecedor deve apresentar, no mínimo, as informações de razão social e CNPJ do fornecedor vinculados ao token recebido, sendo esses campos apenas para visualização. O sistema deve validar o token de acesso recebido antes de permitir a autenticação do fornecedor. Após a autenticação, o fornecedor deve possuir acesso apenas às cotações vinculadas ao token recebido. Enquanto a cotação estiver aberta, o fornecedor deve poder preencher e atualizar sua proposta comercial utilizando o mesmo acesso. Após o encerramento da cotação ou invalidação do token, o fornecedor não deverá mais possuir acesso à área de resposta da cotação. |  |  |  |  |  |  |

Tabela 10 \- Módulo de Login de Fornecedor

| REQUISITO FUNCIONAL 10 |  |  |  |  |  |  |
| ----- | :---- | ----- | :---- | :---- | ----- | ----- |
| Identificação: | RF10 |  |  |  |  |  |
| Descrição: | Módulo de Preenchimento de Cotações para Fornecedores |  |  |  |  |  |
| Responsável: | Elenilton |  |  | Local: |  |  |
| Fonte do Requisito: | Matheus |  |  | Data: | 14/05/2026 |  |
| Prioridade: | Alta |  |  | Dificuldade: | Intermediário |  |
| Especificação |  |  |  |  |  |  |
| O sistema deve permitir que fornecedores autenticados por meio do link individual e token temporário tenham acesso exclusivamente às cotações vinculadas ao seu acesso. O sistema deve permitir que o fornecedor preencha sua proposta comercial informando, no mínimo, produto ofertado, modelo, quantidade disponível, valor unitário, modalidade do frete, transportadora, prazo de entrega, condição de pagamento, impostos, taxas adicionais, validade da proposta, garantia e observações complementares. O sistema deve permitir que o fornecedor atualize sua proposta enquanto a cotação permanecer com status aberta. Apenas a última proposta enviada pelo fornecedor deve ser considerada como resposta válida da cotação, mantendo registradas em histórico todas as versões anteriores submetidas. O sistema deve registrar todas as alterações realizadas nas respostas das cotações, armazenando os dados alterados, os novos valores, data da alteração e identificação do fornecedor responsável pela operação. O sistema deve notificar automaticamente o comprador responsável sempre que uma proposta for enviada ou atualizada por um fornecedor. |  |  |  |  |  |  |

Tabela 11 \- Módulo de Preenchimento de Cotações para Fornecedores

| REQUISITO FUNCIONAL 11 |  |  |  |  |  |  |
| ----- | :---- | ----- | :---- | :---- | ----- | ----- |
| Identificação: | RF11 |  |  |  |  |  |
| Descrição: | Módulo de Criação e Gerenciamento de Ordens de Compras |  |  |  |  |  |
| Responsável: | Elenilton |  |  | Local: |  |  |
| Fonte do Requisito: | Matheus |  |  | Data: | 14/05/2026 |  |
| Prioridade: | Alta |  |  | Dificuldade: | Intermediário |  |
| Especificação |  |  |  |  |  |  |
| O sistema deve permitir que usuários com perfil de comprador criem e gerenciem ordens de compra a partir de cotações concluídas ou solicitações autorizadas. Quando uma proposta de cotação for definida como vencedora, o sistema deve gerar automaticamente uma ordem de compra utilizando as informações da proposta selecionada. O sistema deve permitir que compradores gerem ordens de compra diretamente a partir de solicitações autorizadas, sem necessidade de cotação prévia, quando aplicável ao processo de compra. A ordem de compra deve conter, no mínimo, fornecedor vinculado, data de emissão, produtos, quantidades, valores, condição de pagamento, modalidade de frete, prazo de entrega e status. O status da ordem de compra deve permitir, no mínimo, os estados: aberta, autorizada, enviada, parcialmente atendida, concluída e cancelada. O sistema deve permitir que compradores editem ordens de compra enquanto estiverem com status aberta. Uma ordem de compra somente poderá ser enviada ao fornecedor após autorização do gerente de compras, devendo a autorização ser registrada no sistema. O sistema deve permitir que o gerente de compras remova a autorização da ordem enquanto ela ainda não tiver sido enviada ao fornecedor, permitindo novas edições pelo comprador. O sistema deve permitir o envio automático da ordem de compra ao fornecedor por e-mail ou sua exportação em formato PDF para envio manual pelo comprador O sistema deve permitir o cancelamento parcial ou total dos itens de uma ordem de compra. O sistema deve controlar individualmente o status de atendimento dos itens da ordem de compra com base nos vínculos realizados nas notas fiscais. Quando apenas parte dos itens da ordem de compra tiver sido vinculada a notas fiscais, o sistema deve atualizar automaticamente o status da ordem para parcialmente atendida. Quando todos os itens da ordem de compra forem vinculados a notas fiscais, o sistema deve atualizar automaticamente o status da ordem para concluída. Caso todos os itens da ordem sejam cancelados, o sistema deve alterar automaticamente o status da ordem para cancelada e notificar o fornecedor responsável. O sistema deve permitir a listagem, pesquisa e geração de relatórios de ordens de compra, respeitando as permissões definidas pelo perfil de acesso. Todas as alterações realizadas nas ordens de compra devem ser registradas em histórico, armazenando os dados alterados, os novos valores, data da alteração e identificação do usuário responsável pela operação. |  |  |  |  |  |  |

Tabela 12 \- Módulo de Criação e Gerenciamento de Ordens de Compras

| REQUISITO FUNCIONAL 12 |  |  |  |  |  |  |
| ----- | :---- | ----- | :---- | :---- | ----- | ----- |
| Identificação: | RF12 |  |  |  |  |  |
| Descrição: | Módulo de Gerenciamento de Notas Fiscais de Terceiros |  |  |  |  |  |
| Responsável: | Elenilton |  |  | Local: |  |  |
| Fonte do Requisito: | Matheus |  |  | Data: | 14/05/2026 |  |
| Prioridade: | Alta |  |  | Dificuldade: | Intermediário |  |
| Especificação |  |  |  |  |  |  |
| O sistema deve permitir que usuários vinculados ao setor de contabilidade realizem o cadastro, importação e gerenciamento de notas fiscais emitidas em nome da empresa. O cadastro da nota fiscal deve conter, no mínimo, chave de acesso, data de emissão, número da nota, fornecedor, modalidade de frete, produtos, quantidades, peso, valor unitário, valor total, impostos, taxas adicionais, natureza da operação, transportadora, observações e status. O sistema não deve permitir o cadastro duplicado de notas fiscais com a mesma chave de acesso. O sistema deve permitir o vínculo de uma ou mais ordens de compra a uma nota fiscal, bem como permitir que uma mesma ordem de compra esteja associada a diferentes notas fiscais, quando aplicável. O sistema deve validar os itens vinculados entre ordens de compra e notas fiscais, permitindo o acompanhamento do processo de recebimento e faturamento dos produtos. O sistema deve informar ao módulo de ordens de compra quando os itens forem vinculados a notas fiscais, permitindo a atualização automática do status da ordem conforme o atendimento dos itens. O sistema deve permitir a importação de dados de notas fiscais por meio de arquivos eletrônicos compatíveis com os padrões utilizados pela Nota Fiscal Eletrônica (NF-e). O sistema deve permitir a listagem, pesquisa e geração de relatórios de notas fiscais cadastradas, respeitando as permissões definidas pelo perfil de acesso. Usuários vinculados aos setores de contabilidade, compras e administração devem possuir acesso às notas fiscais vinculadas aos processos de compra. Todas as alterações realizadas nas notas fiscais devem ser registradas em histórico, armazenando os dados alterados, os novos valores, data da alteração e identificação do usuário responsável pela operação. Em caso de divergência entre os dados importados do arquivo NF-e e as informações das ordens de compra vinculadas, o sistema deve alertar o usuário responsável, permitindo a revisão antes da confirmação do vínculo. |  |  |  |  |  |  |

Tabela 13 \- Módulo de Gerenciamento de Notas Fiscais de Terceiros

## **2.3. REQUISITOS NÃO FUNCIONAIS** {#2.3.-requisitos-não-funcionais}

| REQUISITO NÃO FUNCIONAL 01 |  |  |  |  |  |  |
| ----- | :---- | ----- | :---- | :---- | ----- | ----- |
| Identificação: | RNF01 |  |  |  |  |  |
| Descrição: | Segurança e Controle de Acesso |  |  |  |  |  |
| Responsável: | Matheus |  |  | Local: |  |  |
| Fonte do Requisito: | Elenilton |  |  | Data: | 14/05/2026 |  |
| Prioridade: | Alta |  |  | Dificuldade: | Intermediária |  |
| Especificação |  |  |  |  |  |  |
| O sistema deve garantir que apenas usuários autenticados e autorizados tenham acesso aos módulos, funcionalidades e operações permitidos conforme seus perfis de acesso e permissões vinculadas. As senhas dos usuários devem ser armazenadas utilizando algoritmos de hash seguros, e o sistema deve encerrar automaticamente sessões inativas após um período de tempo configurável. |  |  |  |  |  |  |

Tabela 14 \- Segurança e Controle de Acesso

| REQUISITO NÃO FUNCIONAL 02 |  |  |  |  |  |  |
| ----- | :---- | ----- | :---- | :---- | ----- | ----- |
| Identificação: | RNF02 |  |  |  |  |  |
| Descrição: | Proteção de Dados e Conformidade com a LGPD |  |  |  |  |  |
| Responsável: | Matheus |  |  | Local: |  |  |
| Fonte do Requisito: | Matheus |  |  | Data: | 14/05/2026 |  |
| Prioridade: | Alta |  |  | Dificuldade: | Intermediária |  |
| Especificação |  |  |  |  |  |  |
| O sistema deve tratar os dados pessoais coletados em conformidade com a Lei Geral de Proteção de Dados (Lei nº 13.709/2018), limitando o acesso às informações pessoais estritamente ao necessário para cada finalidade. Dados de usuários e fornecedores inativados devem ser retidos apenas pelo prazo operacionalmente necessário, e o sistema não deve compartilhar informações pessoais com terceiros além do estritamente exigido pelo fluxo de cotações. |  |  |  |  |  |  |

Tabela 15 \- Proteção de Dados e Conformidade com a LGPD

| REQUISITO NÃO FUNCIONAL 03 |  |  |  |  |  |  |
| ----- | :---- | ----- | :---- | :---- | ----- | ----- |
| Identificação: | RNF03 |  |  |  |  |  |
| Descrição: | Desempenho e Tempo de Resposta |  |  |  |  |  |
| Responsável: | Matheus |  |  | Local: |  |  |
| Fonte do Requisito: | Elenilton |  |  | Data: | 14/05/2026 |  |
| Prioridade: | Alta |  |  | Dificuldade: | Intermediária |  |
| Especificação |  |  |  |  |  |  |
| O sistema deve manter desempenho adequado durante operações de cadastro, pesquisa, geração de relatórios e processamento de cotações, suportando múltiplos acessos simultâneos sem degradação significativa da experiência do usuário. As operações de cadastro e pesquisa devem retornar resultados em até 3 segundos para a maioria das requisições em condições normais de uso. |  |  |  |  |  |  |

Tabela 16 \- Desempenho e Tempo de Resposta

| REQUISITO NÃO FUNCIONAL 04 |  |  |  |  |  |  |
| ----- | :---- | ----- | :---- | :---- | ----- | ----- |
| Identificação: | RNF04 |  |  |  |  |  |
| Descrição: | Disponibilidade e Confiabilidade do Sistema |  |  |  |  |  |
| Responsável: | Matheus |  |  | Local: |  |  |
| Fonte do Requisito: | Matheus |  |  | Data: | 14/05/2026 |  |
| Prioridade: | Alta |  |  | Dificuldade: | Intermediária |  |
| Especificação |  |  |  |  |  |  |
| O sistema deve permanecer disponível para utilização durante os períodos operacionais da empresa, minimizando falhas, indisponibilidades e perda de informações durante o uso da plataforma. O sistema deve manter disponibilidade de no mínimo 99% durante os horários operacionais da empresa. |  |  |  |  |  |  |

Tabela 17 \- Disponibilidade e Confiabilidade do Sistema

| REQUISITO NÃO FUNCIONAL 05 |  |  |  |  |  |  |
| ----- | :---- | ----- | :---- | :---- | ----- | ----- |
| Identificação: | RNF05 |  |  |  |  |  |
| Descrição: | Auditoria e Rastreabilidade de Operações |  |  |  |  |  |
| Responsável: | Matheus |  |  | Local: |  |  |
| Fonte do Requisito: | Matheus |  |  | Data: | 14/05/2026 |  |
| Prioridade: | Alta |  |  | Dificuldade: | Intermediária |  |
| Especificação |  |  |  |  |  |  |
| O sistema deve registrar operações relevantes realizadas pelos usuários, permitindo rastrear alterações, responsáveis, datas e informações modificadas nos processos da plataforma. |  |  |  |  |  |  |

Tabela 18 \- Auditoria e Rastreabilidade de Operações

| REQUISITO NÃO FUNCIONAL 06 |  |  |  |  |  |  |
| ----- | :---- | ----- | :---- | :---- | ----- | ----- |
| Identificação: | RNF06 |  |  |  |  |  |
| Descrição: | Integridade e Consistência dos Dados |  |  |  |  |  |
| Responsável: | Matheus |  |  | Local: |  |  |
| Fonte do Requisito: | Matheus |  |  | Data: | 14/05/2026 |  |
| Prioridade: | Alta |  |  | Dificuldade: | Intermediária |  |
| Especificação |  |  |  |  |  |  |
| O sistema deve garantir a integridade e consistência das informações armazenadas, evitando duplicidade, perda ou inconsistência de dados entre solicitações, cotações, ordens de compra e notas fiscais. |  |  |  |  |  |  |

Tabela 19 \- Integridade e Consistência dos Dados

| REQUISITO NÃO FUNCIONAL 07 |  |  |  |  |  |  |
| ----- | :---- | ----- | :---- | :---- | ----- | ----- |
| Identificação: | RNF07 |  |  |  |  |  |
| Descrição: | Backup e Recuperação de Dados |  |  |  |  |  |
| Responsável: | Matheus |  |  | Local: |  |  |
| Fonte do Requisito: | Matheus |  |  | Data: | 14/05/2026 |  |
| Prioridade: | Alta |  |  | Dificuldade: | Intermediária |  |
| Especificação |  |  |  |  |  |  |
| O sistema deve possuir mecanismos de backup e recuperação de dados que permitam restaurar informações importantes em caso de falhas, perdas acidentais ou problemas operacionais. Os backups devem ser realizados diariamente, e o sistema deve ser capaz de restaurar as informações em um prazo máximo compatível com as necessidades operacionais da empresa. |  |  |  |  |  |  |

Tabela 20 \- Backup e Recuperação de Dados

| REQUISITO NÃO FUNCIONAL 08 |  |  |  |  |  |  |
| ----- | :---- | ----- | :---- | :---- | ----- | ----- |
| Identificação: | RNF08 |  |  |  |  |  |
| Descrição: | Responsividade e Compatibilidade da Interface |  |  |  |  |  |
| Responsável: | Matheus |  |  | Local: |  |  |
| Fonte do Requisito: | Matheus |  |  | Data: | 14/05/2026 |  |
| Prioridade: | Alta |  |  | Dificuldade: | Intermediária |  |
| Especificação |  |  |  |  |  |  |
| O sistema deve possuir interface responsiva e compatível com os principais navegadores modernos, permitindo utilização adequada em diferentes resoluções de tela e dispositivos compatíveis. |  |  |  |  |  |  |

Tabela 21 \- Responsividade e Compatibilidade da Interface

| REQUISITO NÃO FUNCIONAL 09 |  |  |  |  |  |  |
| ----- | :---- | ----- | :---- | :---- | ----- | ----- |
| Identificação: | RNF09 |  |  |  |  |  |
| Descrição: | Banco de Dados Escalável e Otimizado |  |  |  |  |  |
| Responsável: | Matheus |  |  | Local: |  |  |
| Fonte do Requisito: | Matheus |  |  | Data: | 14/05/2026 |  |
| Prioridade: | Alta |  |  | Dificuldade: | Intermediária |  |
| Especificação |  |  |  |  |  |  |
| O sistema deve possuir um banco de dados estruturado de forma escalável e otimizada, garantindo desempenho adequado no armazenamento e consulta das informações mesmo com o crescimento do volume de dados. A estrutura do banco deve ser projetada para suportar o aumento progressivo de registros sem comprometer a integridade, a consistência e o tempo de resposta das operações realizadas na plataforma.  |  |  |  |  |  |  |

Tabela 22 \- Banco de Dados Escalável e Otimizado

3. # **GERÊNCIA DO PROJETO**  {#gerência-do-projeto}

O projeto SCOPi foi integrado entre as disciplinas de Programação Web 2, Gerência de Projetos de Software e Serviços de Redes de Computadores do curso de Sistemas de Informação do IF Goiano \- Campus Ceres. A disciplina de Programação Web 2 ficou responsável pelo desenvolvimento do sistema web e implementação das funcionalidades da aplicação. A disciplina de Gerência de Projetos concentrou-se no planejamento, organização e acompanhamento das atividades do projeto. Já a disciplina de Serviços de Redes ficou responsável pela infraestrutura da aplicação, utilizando containers Docker, proxy reverso Nginx e banco de dados MySQL. 

## **3.1. PLANEJAMENTO** {#3.1.-planejamento}

O planejamento do projeto foi estruturado com base nos requisitos levantados e nas exigências das três disciplinas integradoras. O escopo do produto foi delimitado ao ciclo completo de compras corporativas, abrangendo os doze requisitos funcionais e nove requisitos não funcionais documentados neste trabalho. Os recursos do projeto compreendem uma equipe de três integrantes — Elenilton Filho, responsável pelo back-end; Giovana Lyssa, responsável pelo front-end; e Matheus Vieira, responsável pelo banco de dados e infraestrutura —, além das ferramentas GitHub para versionamento, Figma para prototipação e Docker para o ambiente de execução.

Os entregáveis do projeto compreendem: a documentação completa do sistema com o Diagrama de Entidade e Relacionamentos (DER); o sistema web funcional desenvolvido em PHP com banco de dados MySQL; a interface responsiva desenvolvida em HTML, CSS e JavaScript; o arquivo docker-compose.yml com a infraestrutura de containers configurada; o relatório técnico de infraestrutura descrevendo as configurações do servidor web e do serviço de arquivos NFS; o repositório público no GitHub contendo todo o código-fonte produzido ao longo do projeto; e o registro de lições aprendidas ao encerramento do projeto. 

## **3.2. ORGANIZAÇÃO E RECURSOS** {#3.2.-organização-e-recursos}

Ferramenta de gestão escolhida (ex: Trello, Jira), divisão de responsabilidades entre os membros e como as tarefas foram atribuídas e acompanhadas. Recursos \= Definir recursos (tempo, pessoas, ferramentas) 

## **3.3. EXECUÇÃO E MONITORAMENTO** {#3.3.-execução-e-monitoramento}

Diagrama hierárquico decompondo o projeto em entregas menores e gerenciáveis — fases, módulos e tarefas. 

4. # **EAP \- ESTRUTURA ANALÍTICA DO PROJETO** {#eap---estrutura-analítica-do-projeto}

## **4.1 QUADRO EAP** {#4.1-quadro-eap}

**![][image1]**

Imagem 01 \- Quadro EAP

## **4.2. DICIONÁRIO DA EAP**  {#4.2.-dicionário-da-eap}

| Atividade | Descrição (Entregáveis) | Responsável | Critérios de Aceitação |
| ----- | ----- | ----- | ----- |
| 1.1.1. Levantamento de Requisitos | Identificação e documentação dos requisitos funcionais (RF01–RF12) e não funcionais (RNF01–RNF09) do sistema. | Elenilton / Matheus | Documento de requisitos aprovado pelos orientadores. |
| 1.1.2. EAP e Dicionário | Elaboração da EAP, do dicionário da EAP com todos os pacotes de trabalho, responsáveis e critérios de aceitação. | Elenilton / Giovana | EAP e dicionário aprovados pelo Prof. Paulo Henrique. |
| 1.1.3. Cronograma | Definição do cronograma do projeto com prazos, marcos e dependências entre atividades. | Elenilton / Giovana | Cronograma aprovado pelo Prof. Paulo Henrique; contempla todas as etapas até junho/2026. |
| 1.1.4. Plano de Riscos | Identificação, análise e plano de resposta aos riscos do projeto (técnicos, de prazo e de recursos). | Elenilton / Matheus | Plano aprovado com pelo menos 5 riscos identificados, probabilidade, impacto e resposta definidos. |
| 1.1.5. Modelo Canvas | Elaboração do Business Model Canvas descrevendo proposta de valor, segmentos, canais, recursos e parceiros do sistema SCOPi. | Giovana | Canvas aprovado pelo Prof. Paulo Henrique; todos os 9 quadrantes preenchidos de forma consistente. |
| 1.1.6. Topologia de Rede e Arquitetura de Containers | Desenho da topologia lógica da rede e da arquitetura de containers (Nginx, PHP, MySQL); inclui diagrama de comunicação entre os serviços Docker. | Matheus | Diagrama aprovado pelo Prof. Roitier; contempla Nginx (proxy reverso), container PHP e container MySQL com volume NFS. |
| 1.2.1. Modelagem de Dados (DER) | Construção do Diagrama de Entidade e Relacionamentos compatível com o banco MySQL. | Matheus | Modelo lógico aprovado e consistente com os requisitos funcionais levantados. |
| 1.2.2. Especificação Funcional (RF e RNF) | Mapeamento dos atores (Funcionário, Comprador, Gerente, Fornecedor, Contabilidade) e suas interações; documentação dos requisitos funcionais e não funcionais. | Elenilton | Notação UML correta; cobre todos os perfis de acesso; RFs e RNFs validados pela equipe. |
| 1.3.1. Módulo Front-End | Implementação das interfaces web responsivas baseadas nos protótipos do Figma, utilizando HTML, CSS e JavaScript. | Giovana | Telas implementadas, responsivas e integradas com as APIs do back-end. |
| 1.3.2. Módulo Back-End | Implementação das regras de negócio em PHP, rotas, autenticação, controle de sessão e links temporários para fornecedores. | Elenilton / Matheus | Funcionalidades operando conforme as regras de negócio; testes unitários sem erros críticos. |
| 1.3.3. Banco de Dados (MySQL) | Modelagem física, scripts de criação de tabelas e configuração do banco MySQL. | Matheus | Banco criado e funcional; consultas principais executando sem erros. |
| 1.3.4. Infraestrutura Docker/Nginx | Configuração do docker-compose.yml com containers Nginx (proxy reverso), PHP e MySQL; volume NFS para persistência de uploads. | Matheus | Ambiente iniciando com um único comando; Nginx roteando corretamente; dados persistindo no volume. |
| 1.4.1. Testes Funcionais | Execução de testes em todos os módulos (Cadastro, Cotação, Ordens, Notas Fiscais) verificando os requisitos funcionais. | Equipe do Projeto | Requisitos funcionais RF01–RF12 validados sem erros críticos. |
| 1.4.2. Testes Não Funcionais | Validação de desempenho, disponibilidade, segurança e responsividade conforme RNF01–RNF09. | Matheus | RNFs validados conforme métricas definidas (ex.: respostas em até 3s, disponibilidade ≥ 99%). |
| 1.4.3. Deploy (Docker Compose) | Publicação do sistema via Docker Compose com Nginx, PHP e MySQL configurados; docker-compose.yml versionado no GitHub. | Matheus | Sistema acessível e funcional após execução de um único comando; repositório GitHub atualizado. |
| 1.4.4. Documentação Final e Entrega | Relatório técnico de infraestrutura, manual de uso por perfil, README no GitHub e apresentação acadêmica dos resultados. | Equipe do Projeto | Documentação entregue e aceite formal registrado pelos orientadores até junho/2026. |
| 1.5.1. Validação do Produto Final | Revisão final do sistema com os orientadores; verificação de que todos os requisitos e entregáveis foram atendidos. | Equipe do Projeto | Aceite formal dos orientadores registrado; todos os RFs e RNFs confirmados como atendidos. |
| 1.5.2. Avaliação do Desempenho | Análise do desempenho da equipe: cumprimento de prazos, qualidade das entregas e uso das ferramentas de gestão. | Elenilton | Relatório de desempenho entregue; indicadores de prazo e qualidade documentados. |
| 1.5.3. Registro de Lições Aprendidas | Documentação das lições aprendidas: pontos positivos, dificuldades enfrentadas e recomendações para projetos futuros. | Equipe do Projeto | Documento de lições aprendidas entregue com pelo menos 5 registros detalhados e aceito pelos orientadores. |

Tabela 23 \- Dicionário EAP

5. # **CRONOGRAMA** {#cronograma}

Registro das datas das entregas, reuniões realizadas, decisões tomadas, ajustes no planejamento e acompanhamento dos prazos ao longo do semestre.

6. # **PLANO DE RISCOS** {#plano-de-riscos}

## **6.1. MATRIZ DE RISCO QUANTITATIVA** {#6.1.-matriz-de-risco-quantitativa}

| Probabilidade X Impacto | 1 Insignificante | 2 Pequeno | 3 Moderado | 4 Grande | 5 Catastrófico |
| :---: | :---: | :---: | :---: | :---: | :---: |
| **5 Quase certo** | 5 | 10 | 15 | 20 | 25 |
| **4 Provável** | 4 | 8 | 12 | 16 | 20 |
| **3 Possível** | 3 | 6 | 8 | 12 | 15 |
| **2 Improvável** | 2 | 4 | 6 | 8 | 10 |
| **1 Muito improvável** | 1 | 2 | 3 | 4 | 5 |

|  | Muito baixo: 1–5 |  |  | Baixo: 6–9 |  |  | Médio: 10–15 |  |  | Alto: 16–25 |
| :---- | :---- | :---- | :---- | :---- | :---- | :---- | :---- | :---- | :---- | :---- |

Tabela 24 \- Matriz de Risco Quantitativa

## **6.2. IDENTIFICAÇÃO E ANÁLISE DOS RISCOS**  {#6.2.-identificação-e-análise-dos-riscos}

| RISCO 01 |  |  |  |  |  |
| ----- | ----- | :---- | ----- | :---- | :---- |
| Risco: | Atraso na Entrega de Funcionalidades-Chave |  |  |  |  |
| Probabilidade: | 4 | Impacto: | 5 | Nível de Risco: | 20 (alto) |
| Mitigação: | Dividir entregas em sprints quinzenais com revisão de progresso; priorizar módulos críticos (back-end e integração) nas primeiras iterações. |  |  |  |  |
| Nível pós-mitigação: |  | 12 (médio) |  |  |  |

Tabela 25 \- Atraso na Entrega de Funcionalidades-Chave

| RISCO 02 |  |  |  |  |  |
| ----- | ----- | :---- | ----- | :---- | :---- |
| Risco: | Falta de Comunicação entre os Membros da Equipe |  |  |  |  |
| Probabilidade: | 4 | Impacto: | 4 | Nível de Risco: | 16 (alto) |
| Mitigação: | Realizar reuniões periódicas; definir responsável por cada módulo (Elenilton – back-end, Giovana – front-end, Matheus – BD). |  |  |  |  |
| Nível pós-mitigação: |  | 8 (baixo) |  |  |  |

Tabela 26 \- Falta de Comunicação entre os Membros da Equipe

| RISCO 03 |  |  |  |  |  |
| ----- | ----- | :---- | ----- | :---- | :---- |
| Risco: | Bugs Críticos na Fase Final de Testes |  |  |  |  |
| Probabilidade: | 3 | Impacto: | 5 | Nível de Risco: | 15 (médio) |
| Mitigação: | Incluir ciclos de QA ao final de cada sprint; realizar revisão de código por pares antes de cada merge no repositório. |  |  |  |  |
| Nível pós-mitigação: |  | 9 (baixo) |  |  |  |

Tabela 27 \- Bugs Críticos na Fase Final de Testes

| RISCO 04 |  |  |  |  |  |
| ----- | ----- | :---- | ----- | :---- | :---- |
| Risco: | Indisponibilidade de Membros da Equipe por Imprevistos |  |  |  |  |
| Probabilidade: | 3 | Impacto: | 4 | Nível de Risco: | 12 (médio) |
| Mitigação: | Manter repositório GitHub atualizado diariamente; documentar tarefas para que qualquer membro consiga assumir atividades do outro. |  |  |  |  |
| Nível pós-mitigação: |  | 6 (baixo) |  |  |  |

Tabela 28 \- Indisponibilidade de Membros da Equipe por Imprevistos

| RISCO 05 |  |  |  |  |  |
| ----- | ----- | :---- | ----- | :---- | :---- |
| Risco: | Não Adaptabilidade dos Usuários ao Sistema |  |  |  |  |
| Probabilidade: | 3 | Impacto: | 5 | Nível de Risco: | 15 (médio) |
| Mitigação: | Desenvolver interface intuitiva e responsiva; criar manual de uso por perfil de acesso e entregar junto com a documentação final. |  |  |  |  |
| Nível pós-mitigação: |  | 8 (baixo) |  |  |  |

Tabela 29 \- Não Adaptabilidade dos Usuários ao Sistema

| RISCO 06 |  |  |  |  |  |
| ----- | ----- | :---- | ----- | :---- | :---- |
| Risco: | Falhas no Banco de Dados MySQL ou Perda de Informações |  |  |  |  |
| Probabilidade: | 3 | Impacto: | 5 | Nível de Risco: | 15 (médio) |
| Mitigação: | Configurar backups automáticos diários via volume NFS no Docker; implementar política de restore e testar a recuperação antes da entrega. |  |  |  |  |
| Nível pós-mitigação: |  | 6 (baixo) |  |  |  |

Tabela 30 \- Falhas no Banco de Dados MySQL ou Perda de Informações

| RISCO 07 |  |  |  |  |  |
| ----- | ----- | :---- | ----- | :---- | :---- |
| Risco: | Problemas de Desempenho do Sistema |  |  |  |  |
| Probabilidade: | 3 | Impacto: | 4 | Nível de Risco: | 12 (médio) |
| Mitigação: | Otimizar queries MySQL e configurar corretamente o Nginx no Docker Compose; realizar testes de desempenho com dados simulados antes do deploy. |  |  |  |  |
| Nível pós-mitigação: |  | 6 (baixo) |  |  |  |

Tabela 31 \- Problemas de Desempenho do Sistema

| RISCO 08 |  |  |  |  |  |
| ----- | ----- | :---- | ----- | :---- | :---- |
| Risco: | Falhas na Integração entre Front-End e Back-End |  |  |  |  |
| Probabilidade: | 3 | Impacto: | 4 | Nível de Risco: | 12 (médio) |
| Mitigação: | Padronizar contratos de comunicação entre as camadas antes do desenvolvimento; realizar testes de integração ao final de cada sprint. |  |  |  |  |
| Nível pós-mitigação: |  | 5 (muito baixo) |  |  |  |

Tabela 32 \- Falhas na Integração entre Front-End e Back-End

| RISCO 09 |  |  |  |  |  |
| ----- | ----- | :---- | ----- | :---- | :---- |
| Risco: | Perda de Dados ou Falha no Registro de Histórico |  |  |  |  |
| Probabilidade: | 2 | Impacto: | 5 | Nível de Risco: | 10 (médio) |
| Mitigação: | Implementar logs automáticos para todas as operações críticas (cotações, ordens, notas fiscais); validar integridade dos dados em cada módulo. |  |  |  |  |
| Nível pós-mitigação: |  | 4 (muito baixo) |  |  |  |

Tabela 33 \- Perda de Dados ou Falha no Registro de Histórico

| RISCO 10 |  |  |  |  |  |
| ----- | ----- | :---- | ----- | :---- | :---- |
| Risco: | Erro na Geração Automática de Ordens de Compra |  |  |  |  |
| Probabilidade: | 3 | Impacto: | 5 | Nível de Risco: | 15 (médio) |
| Mitigação: | Documentar e revisar todas as regras de negócio com os orientadores antes da implementação; criar casos de teste específicos para esse fluxo. |  |  |  |  |
| Nível pós-mitigação: |  | 7 (baixo) |  |  |  |

Tabela 34 \- Erro na Geração Automática de Ordens de Compra

| RISCO 11 |  |  |  |  |  |
| ----- | ----- | :---- | ----- | :---- | :---- |
| Risco: | Envio Incorreto de Cotações para Fornecedores Errados |  |  |  |  |
| Probabilidade: | 3 | Impacto: | 4 | Nível de Risco: | 12 (médio) |
| Mitigação: | Implementar tela obrigatória de confirmação listando fornecedores selecionados antes de qualquer envio; tornar impossível enviar cotação sem confirmação explícita do comprador. |  |  |  |  |
| Nível pós-mitigação: |  | 6 (baixo) |  |  |  |

Tabela 35 \- Envio Incorreto de Cotações para Fornecedores Errados

| RISCO 12 |  |  |  |  |  |
| ----- | ----- | :---- | ----- | :---- | :---- |
| Risco: | Falha no Envio de E-mails com Links de Cotação |  |  |  |  |
| Probabilidade: | 4 | Impacto: | 4 | Nível de Risco: | 16 (alto) |
| Mitigação: | Utilizar PHPMailer com SMTP confiável; implementar reenvio manual pelo comprador e alerta de falha no painel. |  |  |  |  |
| Nível pós-mitigação: |  | 8 (baixo) |  |  |  |

Tabela 36 \- Falha no Envio de E-mails com Links de Cotação

| RISCO 13 |  |  |  |  |  |
| ----- | ----- | :---- | ----- | :---- | :---- |
| Risco: | Links de Acesso de Fornecedores Expirarem antes do Uso |  |  |  |  |
| Probabilidade: | 3 | Impacto: | 4 | Nível de Risco: | 12 (médio) |
| Mitigação: | Tornar o prazo de expiração dos tokens configurável pelo comprador; implementar reenvio de link com um clique no sistema. |  |  |  |  |
| Nível pós-mitigação: |  | 6 (baixo) |  |  |  |

Tabela 37 \- Links de Acesso de Fornecedores Expirarem antes do Uso

| RISCO 14 |  |  |  |  |  |
| ----- | ----- | :---- | ----- | :---- | :---- |
| Risco: | Erro na Comparação Automática de Propostas |  |  |  |  |
| Probabilidade: | 3 | Impacto: | 5 | Nível de Risco: | 15 (médio) |
| Mitigação: | Testar os critérios de comparação com dados reais simulados; permitir que o comprador faça a análise manual como alternativa de segurança. |  |  |  |  |
| Nível pós-mitigação: |  | 7 (baixo) |  |  |  |

Tabela 38 \- Erro na Comparação Automática de Propostas

| RISCO 15 |  |  |  |  |  |
| ----- | ----- | :---- | ----- | :---- | :---- |
| Risco: | Cadastro Incorreto de Fornecedores ou Produtos |  |  |  |  |
| Probabilidade: | 4 | Impacto: | 3 | Nível de Risco: | 12 (médio) |
| Mitigação: | Implementar validações obrigatórias de campo (formato, duplicidade, campos vazios) nos formulários; bloquear o envio enquanto houver erro. |  |  |  |  |
| Nível pós-mitigação: |  | 6 (baixo) |  |  |  |

Tabela 39 \- Cadastro Incorreto de Fornecedores ou Produtos

| RISCO 16 |  |  |  |  |  |
| ----- | ----- | :---- | ----- | :---- | :---- |
| Risco: | Falta de Autorização Gerencial em Tempo Hábil |  |  |  |  |
| Probabilidade: | 3 | Impacto: | 4 | Nível de Risco: | 12 (médio) |
| Mitigação: | Implementar notificações automáticas por e-mail e alertas no painel para gerentes com pendências; registrar data e responsável por cada aprovação. |  |  |  |  |
| Nível pós-mitigação: |  | 5 (muito baixo) |  |  |  |

Tabela 40 \- Falta de Autorização Gerencial em Tempo Hábil

| RISCO 17 |  |  |  |  |  |
| ----- | ----- | :---- | ----- | :---- | :---- |
| Risco: | Inconsistência no Vínculo entre Notas Fiscais e Ordens de Compra |  |  |  |  |
| Probabilidade: | 3 | Impacto: | 5 | Nível de Risco: | 15 (médio) |
| Mitigação: | Bloquear o registro de nota fiscal sem ordem de compra correspondente ativa; a validação é automática e impede fisicamente a inconsistência. |  |  |  |  |
| Nível pós-mitigação: |  | 7 (baixo) |  |  |  |

Tabela 41 \- Inconsistência no Vínculo entre Notas Fiscais e Ordens de Compra

| RISCO 18 |  |  |  |  |  |
| ----- | ----- | :---- | ----- | :---- | :---- |
| Risco: | Sobrecarga do Sistema em Horários de Pico |  |  |  |  |
| Probabilidade: | 3 | Impacto: | 4 | Nível de Risco: | 12 (médio) |
| Mitigação: | Configurar o Nginx como proxy reverso com balanceamento adequado no Docker Compose; realizar testes de carga simulando múltiplos acessos simultâneos antes da entrega. |  |  |  |  |
| Nível pós-mitigação: |  | 6 (baixo) |  |  |  |

Tabela 42 \- Sobrecarga do Sistema em Horários de Pico

| RISCO 19 |  |  |  |  |  |
| ----- | ----- | :---- | ----- | :---- | :---- |
| Risco: | Falta de Rastreabilidade em Alterações Críticas |  |  |  |  |
| Probabilidade: | 2 | Impacto: | 5 | Nível de Risco: | 10 (médio) |
| Mitigação: | Garantir registro de logs completos para todas as operações (cotações, ordens, notas, cadastros); incluir histórico de alterações na documentação final. |  |  |  |  |
| Nível pós-mitigação: |  | 4 (muito baixo) |  |  |  |

Tabela 43 \- Falta de Rastreabilidade em Alterações Críticas

| RISCO 20 |  |  |  |  |  |
| ----- | ----- | :---- | ----- | :---- | :---- |
| Risco: | Falha na Configuração ou Execução do Ambiente Docker Compose |  |  |  |  |
| Probabilidade: | 4 | Impacto: | 5 | Nível de Risco: | 20 (alto) |
| Mitigação: | Documentar e versionar o docker-compose.yml no repositório GitHub; testar a inicialização completa do ambiente (Nginx, PHP, MySQL) em diferentes máquinas antes de cada entrega. |  |  |  |  |
| Nível pós-mitigação: |  | 10 (médio) |  |  |  |

Tabela 44 \- Falha na Configuração ou Execução do Ambiente Docker Compose

| RISCO 21 |  |  |  |  |  |
| ----- | ----- | :---- | ----- | :---- | :---- |
| Risco: | Comprometimento de Token de Acesso de Fornecedor |  |  |  |  |
| Probabilidade: | 2 | Impacto: | 5 | Nível de Risco: | 10 (médio) |
| Mitigação: | Implementar tokens temporários com expiração automática vinculados à cotação; invalidar tokens anteriores ao gerar novo acesso e garantir comunicação exclusivamente via HTTPS. |  |  |  |  |
| Nível pós-mitigação: |  | 4 (muito baixo) |  |  |  |

Tabela 45 \- Comprometimento de Token de Acesso de Fornecedor

| RISCO 22 |  |  |  |  |  |
| ----- | ----- | :---- | ----- | :---- | :---- |
| Risco: | Não Conformidade com a LGPD no Tratamento de Dados de Fornecedores |  |  |  |  |
| Probabilidade: | 2 | Impacto: | 4 | Nível de Risco: | 8 (baixo) |
| Mitigação: | Limitar o acesso a dados pessoais ao estritamente necessário para o fluxo de cotações; definir prazo de retenção dos dados e documentar a finalidade de cada dado coletado. |  |  |  |  |
| Nível pós-mitigação: |  | 4 (muito baixo) |  |  |  |

Tabela 46 \- Não Conformidade com a LGPD no Tratamento de Dados de Fornecedores

## **6.3. PLANO DE AÇÃO** {#6.3.-plano-de-ação}

| Risco | Tipo de Resposta | Ação Proposta | Responsável | Prazo ou Condição de Execução |
| ----- | :---: | ----- | ----- | ----- |
| Atraso na Entrega de Funcionalidades-Chave | Mitigar | Dividir entregas em sprints quinzenais com revisão de progresso; priorizar módulos críticos (back-end e integração) nas primeiras iterações. | Elenilton (Líder de equipe) | Início de cada sprint; revisão quinzenal |
| Falta de Comunicação entre os Membros da Equipe | Evitar | Realizar reuniões periódicas; definir responsável por cada módulo (Elenilton – back-end, Giovana – front-end, Matheus – BD). | Toda a equipe | Periodicamente, conforme necessidade |
| Bugs Críticos na Fase Final de Testes | Mitigar | Incluir ciclos de QA ao final de cada sprint; realizar revisão de código por pares antes de cada merge no repositório. | Equipe do Projeto | Ao final de cada sprint |
| Indisponibilidade de Membros da Equipe por Imprevistos | Aceitar | Manter repositório GitHub atualizado diariamente; documentar tarefas para que qualquer membro consiga assumir atividades do outro. | Todos os membros | Ao final de cada dia de trabalho |
| Não Adaptabilidade dos Usuários ao Sistema | Mitigar | Desenvolver interface responsiva; criar manual de uso por perfil de acesso e entregar junto à documentação final. | Giovana (Front-end) / Equipe | Durante o desenvolvimento front-end e antes da entrega final |
| Falhas no Banco de Dados MySQL ou Perda de Informações | Mitigar | Configurar backups automáticos diários via volume NFS no Docker; implementar política de restore e testar recuperação antes da entrega. | Matheus (Banco de Dados) | Antes da entrega; verificar mensalmente |
| Problemas de Desempenho do Sistema | Mitigar | Otimizar queries MySQL; configurar corretamente o Nginx no Docker Compose; realizar testes de desempenho com dados simulados. | Matheus (BD) / Elenilton (Back-end) | Antes da entrega final |
| Falhas na Integração entre Front-End e Back-End | Evitar | Padronizar contratos de comunicação entre as camadas antes do desenvolvimento; realizar testes de integração ao final de cada sprint. | Elenilton (Back-end) / Giovana (Front-end) | Antes do início de cada módulo |
| Perda de Dados ou Falha no Registro de Histórico | Mitigar | Implementar logs automáticos para todas as operações críticas; validar integridade dos dados em cada módulo durante o desenvolvimento. | Elenilton (Back-end) / Matheus (BD) | Durante o desenvolvimento de cada módulo |
| Erro na Geração Automática de Ordens de Compra | Evitar | Documentar e revisar todas as regras de negócio antes da implementação; criar casos de teste específicos para esse fluxo. | Elenilton (Back-end) | Antes da implementação do módulo de ordens |
| Envio Incorreto de Cotações para Fornecedores Errados | Evitar | Implementar tela obrigatória de confirmação listando fornecedores selecionados; tornar impossível enviar cotação sem confirmação explícita do comprador. | Giovana (Front-end) / Elenilton (Back-end) | Durante o desenvolvimento do módulo de cotações |
| Falha no Envio de E-mails com Links de Cotação | Mitigar | Utilizar PHPMailer com SMTP confiável; implementar reenvio manual pelo comprador e alerta de falha no painel. | Elenilton (Back-end) | Antes da entrega; monitorar em produção |
| Links de Acesso de Fornecedores Expirarem antes do Uso | Evitar | Tornar o prazo de expiração dos tokens configurável pelo comprador; implementar reenvio de link com um clique no sistema. | Elenilton (Back-end) / Giovana (Front-end) | Durante implementação do módulo de cotações |
| Erro na Comparação Automática de Propostas | Mitigar | Testar os critérios de comparação com dados reais simulados; permitir análise manual pelo comprador como alternativa de segurança. | Elenilton (Back-end) / Matheus (BD) | Na sprint de testes funcionais |
| Cadastro Incorreto de Fornecedores ou Produtos | Evitar | Implementar validações obrigatórias de campo (formato, duplicidade, campos vazios); bloquear o envio enquanto houver erro. | Giovana (Front-end) / Elenilton (Back-end) | Durante o módulo de cadastros |
| Falta de Autorização Gerencial em Tempo Hábil | Transferir | Implementar notificações automáticas por e-mail e alertas no painel para gerentes com pendências; a responsabilidade de agir dentro do prazo é do gerente. | Gerente de Área (usuário do sistema) | Durante implementação do fluxo de aprovação |
| Inconsistência no Vínculo entre Notas Fiscais e Ordens de Compra | Evitar | Bloquear registro de nota fiscal sem ordem de compra ativa; a validação é automática e impede fisicamente a inconsistência. | Elenilton (Back-end) / Matheus (BD) | Módulo de notas fiscais; validar na sprint de testes |
| Sobrecarga do Sistema em Horários de Pico | Mitigar | Configurar o Nginx como proxy reverso com balanceamento adequado no Docker Compose; realizar testes de carga simulando múltiplos acessos antes da entrega. | Matheus (DevOps) / Elenilton (Back-end) | Antes da entrega final |
| Falta de Rastreabilidade em Alterações Críticas | Mitigar | Garantir logs completos para todas as operações (cotações, ordens, notas, cadastros); incluir histórico de alterações na documentação final. | Elenilton (Back-end) / Equipe | Durante todo o desenvolvimento; revisar na documentação final |
| Falha na Configuração ou Execução do Ambiente Docker Compose | Mitigar | Documentar e versionar o docker-compose.yml no GitHub; testar a inicialização completa do ambiente (Nginx, PHP, MySQL) em diferentes máquinas antes de cada entrega. | Matheus (DevOps) / Elenilton | Antes de cada entrega; obrigatório na entrega final |
| Comprometimento de Token de Acesso de Fornecedor | Mitigar | Implementar tokens com expiração automática; invalidar tokens anteriores ao gerar novo acesso; garantir comunicação via HTTPS. | Elenilton (Back-end) | Durante implementação do módulo de cotações |
| Não Conformidade com a LGPD no Tratamento de Dados de Fornecedores | Mitigar | Limitar acesso a dados pessoais ao estritamente necessário; definir prazo de retenção e documentar a finalidade de cada dado coletado. | Elenilton (Líder) / Toda a equipe | Durante o desenvolvimento e na documentação final |

Tabela 47 \- Plano de Ação dos Riscos

7. # **MODELO CANVAS \- Fazer** {#modelo-canvas---fazer}

Imagem 02 \- Modelo Canvas 

8. # **DER  \- DIAGRAMA DE ENTIDADE E RELACIONAMENTOS \- Fazer** {#der---diagrama-de-entidade-e-relacionamentos---fazer}

[Imagem 03 \- Diagrama de Entidade e Relacionamentos](https://drive.google.com/file/d/1mPBcNabyprm6dGS_E13R3WZpHDTAcTrq/view?usp=sharing)

9. # **TOPOLOGIA DE REDE E ARQUITETURA DE CONTAINERS**  {#topologia-de-rede-e-arquitetura-de-containers}

## **9.1. TOPOLOGIA DE REDE E ARQUITETURA DE CONTAINERS** {#9.1.-topologia-de-rede-e-arquitetura-de-containers}

	A

10. # **DESENVOLVIMENTO E INFRAESTRUTURA**  {#desenvolvimento-e-infraestrutura}

## **10.1. ARQUITETURA DO SOFTWARE** {#10.1.-arquitetura-do-software}

	O SCOPi adota o padrão arquitetural MVC (Model-View-Controller), organizando o código em três camadas: o Model, responsável pela lógica de negócio e acesso ao banco de dados MySQL; a View, composta pelas interfaces em HTML, CSS e JavaScript apresentadas conforme o perfil de acesso de cada usuário; e o Controller, que intermedia as requisições entre as duas camadas, gerenciando o fluxo de estados, o controle de sessão e a validação dos tokens temporários dos fornecedores. Essa separação facilita a manutenção do sistema e sustenta a infraestrutura conteinerizada via Docker Compose adotada no projeto.

## **10.2. BACK-END** {#10.2.-back-end}

Regras de negócio implementadas em PHP, estrutura das rotas, conexão com o banco e funcionalidades principais (CRUD, autenticação, controle de sessão). 

## **10.3. INFRAESTRUTURA E DEPLOY** {#10.3.-infraestrutura-e-deploy}

Arquitetura de containers com Docker Compose — Nginx como proxy reverso, PHP e MySQL — e volume compartilhado via NFS para persistência dos arquivos de upload. 

## **10.4. INTEGRAÇÃO E TESTES** {#10.4.-integração-e-testes}

Integração front-end/back-end, testes realizados (unidade, integração, usabilidade), bugs corrigidos e validação da performance do proxy reverso. 

11. # **ENCERRAMENTO**  {#encerramento}

Validação do produto final, avaliação do desempenho do grupo e registro das lições aprendidas durante o projeto.  

# 

# **REFERÊNCIAS** {#referências}

QUALHATO, Bruno et al. DESENVOLVIMENTO DO SISTEMA WebCotação: APLICAÇÃO NA CRV INDUSTRIAL. 2023\.  
Colocar GitHub  


[image1]: <data:image/png;base64,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>