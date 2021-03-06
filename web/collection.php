<!DOCTYPE html>
<html lang="en" ng-app="bdictCollection">
  <head>
    <meta content="text/html; charset=UTF-8" http-equiv="content-type"/>
    <meta name="fragment" content="!" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="NTI Buddhist Text Reader">
    <link rel="shortcut icon" href="images/yan.png" type="image/jpeg" />
    <title>NTI Buddhist Text Reader</title>
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css">
    <!-- Custom styles for this template -->
    <link rel="stylesheet" href="buddhistdict.css" rel="stylesheet">
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
  </head>
  <body ng-controller="CorpusListCtrl">
    <script>
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
      ga('create', 'UA-57634593-1', 'auto');
      ga('send', 'pageview');
    </script>
    <div class="starter-template">
      <div class="row">
        <div class="span2"><img id="logo" src="images/yan.png" alt="Logo" class="pull-left"/></div>
        <div class="span7"><h1>NTI Buddhist Text Reader</h1></div>
      </div>
    </div>
    <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="index.html">Home</a>
        </div>
        <div class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li class="active"><a href="corpus.html">Texts</a></li>
            <li><a href="tools.html">Tools</a></li>
            <li><a href="dict_resources.html">Resources</a></li>
            <li><a href="about.html">About</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </div>

    <div class="container">
      <h2 ng-if="main_doc.source_name">{{main_doc.source_name}}</h2>
      <p ng-if="main_doc.source">Source: {{main_doc.source}}</p>
      <p ng-if="main_doc.reference">Reference: {{main_doc.reference}}</p>
      <p ng-if="main_doc.period">Period: {{main_doc.period}}</p>
      <p ng-if="main_doc.translator">Compiled / translated by: {{main_doc.translator}}</p>
      <p ng-if="main_doc.genre">Genre: {{main_doc.genre}}</p>
      <p>
        Filter: <input ng-model="query">
      </p>
      <table class="table table-bordered table-hover">
        <thead>
          <tr><th>Name</th><th>Description</th></tr>
        </thead>
        <tbody>
          <tr ng-repeat="doc in docs | filter:query | orderBy:'id'">
            <td ng-if="main_doc.hasOwnProperty('short_name')">
              <a href="{{doc.uri}}" title="Detailed information on the text entry">{{doc.short_name}}</a>
            </td>
            <td ng-if="!main_doc.hasOwnProperty('short_name')">
              <a href="{{doc.uri}}" title="Detailed information on the text entry">{{doc.source_name}}</a>
            </td>
            <td>
              {{doc.description}}
            </td>
          </tr>
        </tbody>
      </table>
      <hr/>
      <p>
        Copyright Nan Tien Institute 2013 - 2015, <a href="http://www.nantien.edu.au/" 
        title="Nan Tien Institute">www.nantien.edu.au</a>.
      </p>
      <p>This page was last updated on December 13, 2014.</p>
    </div>
    <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.0.3/js/bootstrap.min.js"></script>
    <script src="script/angular.min.js"></script>
<?php
    if (isset($_REQUEST['colname'])) {
        $colname = $_REQUEST['colname'];
        print("<input id='colname' type='hidden' name='colname' value='" . $colname . "'/>\n");
    }
?>
    <script src="script/collection_ctl.js"></script>
  </body>
<html>
