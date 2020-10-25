<?php

declare(strict_types=1);

// $Id: install.php,v 1.0 2004/05/15 12:31:24 lehy Exp $
define('_INSTALL_L47', 'Prosseguir');
define('_INSTALL_L0', 'Bem vindo ao instalador do módulo Moodle4xoops 1.2');
define('_INSTALL_L14', 'Próximo passo »');
define('_INSTALL_L70', 'Por gentileza, altere as permissões do arquivo mainarquivo.php de forma que ele se torne executável pelo servidor (por exemplo: chmod 777 mainarquivo.php em um servidor UNIX/LINUX, ou verfique as prorpriedades do arquivo e assegure-se que a opção');
define('_INSTALL_L80', 'Introdução');
define('_INSTALL_L56', 'Caminho virtual do Moodle4Xoops(URL)' . XOOPS_ROOT_PATH . '/modules/moodle');
define('_INSTALL_L58', 'Caminho virtual do Moodle4Xoops SEM a barra final');
define('_INSTALL_L55', 'Caminho fisico do Moodle4Xoops');
define('_INSTALL_L59', 'Caminho fisico do Moodle4Xoops SEM a barra final');
define('_INSTALL_L52', 'Caminho fisico dos arquivos vriados pelo Moodle4Xoops');
define('_INSTALL_L68', 'Caminho fisico onde o Moodle4Xoops salva arquivos');
define('_INSTALL_L29', 'Atributos das pastas criadas pelo Moodle4Xoops');
define('_INSTALL_L64', '0777, quando nao 0750.');
define('_INSTALL_L30', 'Name da pasta do administrador');
define('_INSTALL_L63', 'Alguns servidores reservam para si a URL /admin. Isso gera conflito com o local administrativo padronizado pelo Moodle. Evite isso digitanto um nome diferente aqui');
define('_INSTALL_L200', 'Prefixo para as tabelas do Moodle4Xoops');
define('_INSTALL_L201', 'Prefixo das tabelas que serao instaladas pelo Moodle4Xoops.');
define('_INSTALL_L91', 'confirmar');
define('_INSTALL_L90', 'Configuracao geral');
define('_INSTALL_L81', 'Verifique o atributos do arquivos config.php..');
define('_INSTALL_L82', 'Verifique atributos dos demais arquivos e pastas..');
define('_INSTALL_L83', 'Arquivo %s protegido contra escrita.');
define('_INSTALL_L84', 'Arquivo %s regularmente liberado para escrita.');
define('_INSTALL_L85', 'Pasta %s protegida contra escrita.');
define('_INSTALL_L86', 'Pasta %s regularmente liberada para escrita.');
define('_INSTALL_L87', 'Sem erros detectados.');
define('_INSTALL_L89', 'Parâmetros gerais');

define('_INSTALL_L81', 'Verificando atributos');
define('_INSTALL_L53', 'Gentileza confirmar os seguintes dados:');

define('_INSTALL_L92', 'salva alteracoes');
define('_INSTALL_L93', 'modifica configuracoes');

define('_INSTALL_L88', 'Salvando dados do configurador..');

define('_INSTALL_L125', 'Arquivo %s sobrescrito por %s.');
define('_INSTALL_L126', 'Impedida a escrita do arquivo %s.');

define('_INSTALL_L62', 'Dados do configurador foram salvos no mainfile.php.');
define('_INSTALL_L94', 'verifique o caminho & URL');
define('_INSTALL_L96', 'Existe diferenca entre o caminho fisico detectado (%s) e o caminho digitado.');
define('_INSTALL_L97', '<b>Caminho fisico</b> com erro.');

define('_INSTALL_L99', '<b>Caminho fisico</b> parece ser uma pasta.');
define('_INSTALL_L100', '<b>Caminho virtual</b> corresponde uma URL regular.');
define('_INSTALL_L101', '<b>Caminho virtual</b> corresponde uma URL inexistente.');

define('_INSTALL_L128', 'Escolha o idioma a ser utilizado durante este instalador');

define('_INSTALL_L80', 'iniciando');
define('_INSTALL_L46', "Os seguintes arquivos devem permitir escrita. Mude seus atributos. (ex. comando 'chmod 666 file_name' em servidor UNIX/LINUX, ou desmarcar \"read-only\" para permitir a escrita em servidores Windows)");
define('_INSTALL_L42', 'Voltar');

define('_INSTALL_L60', 'Tentativa de escrita sem sucesso no arquivo config.php. Verifique os atributos e tente novamente.');
define('_INSTALL_L95', 'Tentativa sem sucesso para detectar o caminho fisico da pasta do MOODLE4XOOPS.');
define('_INSTALL_L121', 'Constante %s armazenada em %s.');
define('_INSTALL_L32', 'Instalado completamente');
define('_INSTALL_L320', 'Sistema instalado completamente');

define('_INSTALL_CHARSET', 'ISO-8859-1');

define('_INSTALL_L27', 'Database Hostname');
define('_INSTALL_L67', "Hostname of the database server. If you are not sure, 'localhost' works in most cases.");
define('_INSTALL_L28', 'Database Username');
define('_INSTALL_L65', 'Your database user account on the host');
define('_INSTALL_L54', 'Use persistent connection?');
define('_INSTALL_L69', "Default is 'NO'. Choose 'NO' if you are not sure.");
define('_INSTALL_L552', 'MOODLE4XOOPS Physical Path');
define('_INSTALL_L562', 'MOODLE4XOOPS Virtual Path (URL)');
define('_INSTALL_L592', 'Physical path to your main MOODLE4XOOPS directory WITHOUT trailing slash');
define('_INSTALL_L582', 'Virtual path to your main MOODLE4XOOPS directory WITHOUT trailing slash');
define('_INSTALL_L950', 'Admin path');
define('_INSTALL_L952', 'Data path of Moodle');
define('_INSTALL_L954', 'Access rights');
define('_INSTALL_L951', '... if you have change it, WITHOUT trailing slash.');
define('_INSTALL_L953', 'Directory of storage for Moodle.');
define('_INSTALL_L955', 'Access rights of the files created by Moodle.');
define('_INSTALL_L23', 'Yes');
define('_INSTALL_L24', 'No');
define('_INSTALL_L956', 'Checking data Moodle directory'); // No HTML Code
define('_INSTALL_L563', 'Before continuing the installation, check that the data Moodle directory is well created and has the permissions of writing (0777)!');
define('_INSTALL_L117', 'Finish');
