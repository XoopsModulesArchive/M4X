<?php

declare(strict_types=1);

// $Id: welcome.php,v 1.6 2003/02/12 11:35:36 okazu Exp $
$content .= "<u><b>Que e´ o Moodle?</b></u>
<p>
<b>Moodle.org</b> e´ um sistema de ensino à distância, o presente módulo <b>Moodle4xoops</b> interliga o sistema de ensino ao mecanismo <b>Xoops</b> de criação de sites dinâmicos. Todos os sistemas <a href='http://www.debian.org/intro/free.pt.html' target='_blank'>funcionam sob códigos livres (GPL)</a>e foram concebidos usando programação PHP orientada a objetos.
</p>
<u><b>Requisitos exigidos pelo Xoops e pelo Moodle:</b></u>
<p>
<ul>
<li>Servidor Web (<a href='http://www.apache.org/' target='_blank'>Apache</a>, IIS, Roxen, etc)</li>
<li>servidor <a href='http://www.php.net/' target='_blank'>PHP</a> versao 4.0.5 ou maior (recomendada versao 4.1.1 ou maior)</li>
<li>servidor <a href='http://www.mysql.com/' target='_blank'>MySQL</a> como banco de dados, versao 3.23.XX</li>
</ul>
<u><b>Requisitos exigidos adicionalmente pelo Moodle:</b></u>
<p>
<ul>
<li>Servidor Cron (indispensável)- <a href='http://www.cron-server.de/' target='_blank'>como este externo</a>, <a href='http://www.cron24.de/en/' target='_blank'> e este gratuito sob teste</a></li>
<li>Suporte a gráficos (importante)- a classe de gráficos <a href='http://www.boutell.com/gd/' target='_blank'>GD Library</a> otimiza o desempenho do site de ensino</li>
</ul>

</p>
<u><b>Antes de instalar</b></u>
<ul>
<li>Verifique os dados de instalação de seu servidor Web, PHP e a base de dados MySQL.</li>
<li>Crie uma conta sem usuario específico, apenas para acesso genérico ao banco de dados MySQL.</li>
<li>Mude o atributo do config.php na pasta deste módulo para permitir escrita total (atributo 777).</li>
<li>Mude o atributo dos demais arquivos .php da pasta para permitir execução dos códigos (atributo 755).</li>
<li>Habilite cookie e JavaScript em seu navegador.</li>
</ul>
<u><b>Instalando...</b></u>
<p>
Siga as indicações deste assistente de instalação. Clique em 'Prosseguir' para continuar.
</p>
";
