<?php
use PhpXmlRpc\Value;

if(array_key_exists('rsd', $_GET)) {
  $base = Config::$base;
  header('Content-Type: text/xml; charset=UTF-8');
  echo <<<EOT
<?xml version="1.0" encoding="UTF-8"?><rsd version="1.0" xmlns="http://archipelago.phrasewise.com/rsd">
  <service>
    <engineName>WordPress</engineName>
    <engineLink>$base/</engineLink>
    <homePageLink>https://$domain</homePageLink>
    <apis>
      <api name="WordPress" blogID="1" preferred="true" apiLink="$base/$domain/" />
    </apis>
  </service>
</rsd>
EOT;
  die();
}

function debug($xml) {
  $fp = fopen('logs/xmlrpc.log', 'a');
  fwrite($fp, "---------------------------------------------\n");
  fwrite($fp, date('Y-m-d H:i:s')."\n");
  fwrite($fp, $xml."\n\n");
  fclose($fp);
}

function getUsersBlogs() {
  global $domain;

  $blogs = [
    [
      'blogid' => $domain,
      'url' => 'https://'.$domain.'/',
      'blogName' => $domain,
      'isAdmin' => true,
      'xmlrpc' => Config::$base.'/'.$domain.'/xmlrpc'
    ]
  ];

  $encoder = new PhpXmlRpc\Encoder();
  return new PhpXmlRpc\Response($encoder->encode($blogs));
}
$getUsersBlogs_sig = [
  [Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString]
];
$getUsersBlogs_doc = 'Get a list of the user\'s blogs';



function newPost($req) {
  global $domain;

  debug($req->serialize());

  $blogid = $req->getParam(0)->scalarval();
  $username = $req->getParam(1)->scalarval();
  $password = $req->getParam(2)->scalarval();
  $content = $req->getParam(3);

  // Discover the micropub endpoint
  $micropubEndpoint = IndieAuth\Client::discoverMicropubEndpoint('https://'.$username.'/');

  if(!$micropubEndpoint) {
    $err = 'No Micropub endpoint found';
    return new PhpXmlRpc\Response(0, PhpXmlRpc\PhpXmlRpc::$xmlrpcerruser, $err);
  }

  $name = $content->structmem('title')->scalarval();
  $html = $content->structmem('description')->scalarval();

  $params = [
    'type' => ['h-entry'],
    'properties' => [
      'name' => [$name],
      'content' => [[
        'html' => $html
      ]]
    ]
  ];

  if($content->structmemexists('mt_keywords')) {
    $tags = $content->structmem('mt_keywords');
    $category = [];
    for($i=0; $i<$tags->arraysize(); $i++) {
      $category[] = $tags->arraymem($i)->scalarval();
    }
    $params['properties']['category'] = $category;
  }

  if($content->structmemexists('post_status')) {
    $post_status = $content->structmem('post_status')->scalarval();
    if(in_array($post_status, ['publish', 'draft'])) {
      $params['properties']['post-status'] = [$post_status];
    }
  }

  debug(json_encode($params, JSON_PRETTY_PRINT));

  $http = new p3k\HTTP();
  $response = $http->post($micropubEndpoint, json_encode($params), [
    'Authorization: Bearer '.$password,
    'Content-Type: application/json'
  ]);

  if(isset($response['headers']['Location'])) {
    return new PhpXmlRpc\Value($response['headers']['Location'], "string");
  } else {
    $err = 'Micropub error when publishing';
    return new PhpXmlRpc\Response(0, PhpXmlRpc\PhpXmlRpc::$xmlrpcerruser, $err);
  }
}

$newPost_sig = [
  [Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcStruct]
];
$newPost_doc = 'Create a new blog post';


$srv = new PhpXmlRpc\Server(array(
    "metaWeblog.getUsersBlogs" => array(
        "function" => "getUsersBlogs",
        "signature" => $getUsersBlogs_sig,
        "docstring" => $getUsersBlogs_doc,
    ),
    "wp.getUsersBlogs" => array(
        "function" => "getUsersBlogs",
        "signature" => $getUsersBlogs_sig,
        "docstring" => $getUsersBlogs_doc,
    ),
    "metaWeblog.newPost" => array(
        "function" => "newPost",
        "signature" => $newPost_sig,
        "docstring" => $newPost_doc,
    ),
));

