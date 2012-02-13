<!DOCTYPE html> 
<html lang="he">
  <head>
    <meta name="keywords" content=""/>
    <meta name="description" content=""/>
    <meta name="copyright" content=""/>
    <meta name="author" content=""/>
    <meta name="robots" content=""/>
    <base href="<?=lib('uri')->base_url?>"/>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <meta name="generator" content="Framework::Apex v1.3"/>

    <? lib('html')->auto('js'); ?>

    <? lib('html')->auto('css'); ?>

    <!--[if lt IE 9]><script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->

    <script type="text/javascript">
      $(document).ready(function(){
      });
    </script>

    <script src="http://lesscss.googlecode.com/files/less-1.1.3.min.js"></script>

    <title></title>
  </head>
  <body>
    <? include $template; ?>
    <hr/>
    <a target="_blank" href="http://www.phpapex.com/"><img style="border:0;" src="resources/media/poweredBy.gif"/></a>
    {mem_usage} | {elapsed_time}
  </body>
</html>