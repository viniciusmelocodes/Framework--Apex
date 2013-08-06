<!DOCTYPE html> 
<html lang="he">
  <head>
    <meta name="keywords" content=""/>
    <meta name="description" content=""/>
    <meta name="author" content=""/>
    <meta name="robots" content=""/>
    <base href="<?=lib('uri')->base_url?>"/>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <meta name="generator" content="Framework::Apex v1.4"/>


    <?
    lib('resources')->auto('css');
    lib('resources')->auto('css/controllers');
    lib('resources')->auto('js');
    lib('resources')->auto('js/controllers');
    lib('resources')->tags();
    ?>
    <!--[if lt IE 9]><script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->

    <script type="text/javascript">
      $(document).ready(function(){
        console.log('{mem_usage} | {elapsed_time}');
      });
    </script>

    <title></title>
  </head>
  <body>
    <? include $template; ?>
  </body>
</html>