<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


include('simple_html_dom.php');

function prb(){
    $html=file_get_html("http://journals.aps.org/prb/recent");
    // foreach($pub->find('div.large-9') as $element) {
    //     $item['title']    = $element->find('h5.title a', 0)->plaintext;
    //     $item['authors']  = $element->find('h6.authors', 0)->plaintext;
    //     #$item['abstract'] = $element->find('div.summary p',0))->plaintext;
    //     echo $item['title']."<br/>";
    //     #echo $item['authors'];
    //     }
    echo 'mark';
}


#get keywords string from the search
$keys=$_POST["keys"];

#get or/and user selectrion
$orand=$_POST["orand"];

#convert keys to array
$arr=explode(",",$keys);
$query="";

$size=sizeof($arr);
$i=0;

#prepare query
if ($orand==0){
foreach ($arr as $el){
    $i=$i+1;
    if ($el!="") {
        $query = $query.$el;
    }
    if ($i<=$size-2) {
          $query=$query."+OR+";
    }
}
}else{
foreach ($arr as $el){
    $i=$i+1;
    if ($el!="") {
        $query = $query.$el;
    }
    if ($i<=$size-2) {
          $query=$query."+AND+";
    }
}
}

#number of returned papers is limited to 20
$return="";
$limit=20;

#arxiv api
$url = 'http://export.arxiv.org/api/query?search_query=all:'.$query.'&sortBy=submittedDate&start=0&max_results='.$limit;

#the response is casted into an xml object
$response = file_get_contents($url);
$xml = new SimpleXMLElement($response);

#read information from the xml response
foreach ($xml->entry as $entry){
    $link=$entry->id;
    $start = strpos($link,'abs');
    $linkpdf = substr_replace($link, 'pdf', $start, 3);
    $linkpdf= $linkpdf.'.pdf';
    $title=$entry->title;
    $date = $entry->published;
    $date = substr($date,0,10);
    $abstract=$entry->summary;
    $authors = "";
    foreach ($entry->author as $author){
        $authors .=$author->name.", ";
    }
    $authors = substr($authors,0,strlen($authors)-2);

    $newtitle=$title;
    $newabstract=$abstract;
    #mark the keywords in the title/abstract with a yellow background
    foreach ($arr as $el){
        $rep = "<span style='background-color:yellow;'>".$el."</span> ";
        if (strpos($el,strtolower($title))!=-1) {
            $newtitle = str_replace($el,$rep,strtolower($newtitle));
        }
        if (strpos($el,strtolower($abstract))!=-1) {
            $rep = "<span style='background-color:yellow;'>".$el."</span> ";
            $newabstract = str_replace($el,$rep,strtolower($newabstract));
        }
    }

    #create paper item
    $item='<div class="list-group">
            <div>
                <h6 style="float:right; margin-top:0px;">'.$date.'</h6>
                <h4 class="list-group-item-heading" style="width:87%;"><a href="'.$link.'" target="_blank">'.$newtitle.'</a>
                 <a href="'.$linkpdf.'" target="_blank">[PDF]</a></h4>
                <h5>'.$authors.'</h6>
                <p class="list-group-item-text">'.$newabstract.'</p>
            </div>
           </div>';
    $return = $return.$item;
}

echo $return;

?>
