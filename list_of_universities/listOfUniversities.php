<?php
set_time_limit(1200);
libxml_use_internal_errors(true);

$fileName = 'list_of_universities.xml';

define('FIRST_ROW',1);
define('LAST_ROW',100);

$firstPage = 'http://www.nica.ru/dictionary/accr_reestr_vuz/';
$dataFromFirstPage = file_get_contents($firstPage);

$formOfIncorporation = array('gos','negos'); //gos - Государственные, муниципальные, субъектов РФ;
                                             //negos - Негосударственные

preg_match_all('/<option value="(\d+)">([а-яА-я.\s-]+)/',$dataFromFirstPage,$rawDataRegions);

$dataRegions = array_combine($rawDataRegions[1],$rawDataRegions[2]);

$dom = new DOMDocument('1.0', 'UTF-8');
$rootNode = $dom->appendChild(new DOMElement('root'));

foreach($formOfIncorporation as $typeIncorporation) {

    $incorporation = new DOMElement($typeIncorporation);
    $rootNode = $rootNode->appendChild($incorporation);

    foreach($dataRegions as $key=>$value) {

        $pageURL = 'http://www.nica.ru/dictionary/accr_reestr_vuz/index.php?eo=1&form='.$typeIncorporation.'&region='.$key.'&subname=&btnSearch=1';
        $pageWithListUniversity = getPageWithListUniversity($pageURL); 

        $listURLsOfUniversity  = getListURLsOfUniversity($pageWithListUniversity);
        if ($listURLsOfUniversity) {
            foreach($listURLsOfUniversity as $URL) {
                $urlPageAboutUniversity = $firstPage . $URL;
                $informationAboutUniversity = getDataAboutUniversity($urlPageAboutUniversity,$incorporation);
            }
        }
    }
}

$flag = file_put_contents($fileName,$dom->saveXML());
if($flag) {
    echo 'Information has been successfully written to the file '.$fileName;
}

function getPageWithListUniversity($URL) {

    $stringRequest ='fr='.FIRST_ROW.'&rn='.LAST_ROW; //Получить 100 записей

    $curl=curl_init();
    curl_setopt($curl, CURLOPT_URL, $URL);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $stringRequest);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_TIMEOUT, 250);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:19.0) Gecko/20100101 Firefox/19.0');
    $response=curl_exec($curl);
    $err=curl_error($curl);
    curl_close($curl);

    return $response;
}

function getListURLsOfUniversity($page) {

    $domObject = new domDocument;
    $domObject->loadHTML($page);
    $domObject->preserveWhiteSpace = false;

    $xpathObject = new DOMXPath($domObject);
    $xpathExpression = '/html/body/div/table/tbody/tr/td[2]/table[@class="ou_list"]/tr/td[2]//a';
    $rawListURLOfUniversity = $xpathObject->query($xpathExpression);

    for($i = 0; $i < $rawListURLOfUniversity->length; $i++) {
        $listURLUniversity[] = $rawListURLOfUniversity->item($i)->getAttribute('href');
    }
    return $listURLUniversity;
}

function getDataAboutUniversity($url,$dom) {
    $domObject = new domDocument;
    $domObject->loadHTMLFile($url);
    $domObject->preserveWhiteSpace = false;

    $generalDataNode = $dom->appendChild(new DOMElement('generalDataNode'));
    
    $xpathObject = new DOMXPath($domObject);
    
    $xpathExpression = '/html/body/div/table/tbody/tr/td[2]/div[2]/h2';
    $rawNameUniversity = $xpathObject->query($xpathExpression);
    $nameUniversity = new DOMElement('nameUniversity',$rawNameUniversity->item(0)->nodeValue);
    $generalDataNode->appendChild($nameUniversity);
    
    $xpathExpression = '/html/body/div/table/tbody/tr/td[2]/table[@class="svid_info"]/tr/td[2]';    
    $rawGeneralDataAboutUniversity = $xpathObject->query($xpathExpression);
    
    if ($rawGeneralDataAboutUniversity) {
        
        $legalAddressNode = new DOMElement('legalAddress',$rawGeneralDataAboutUniversity->item(0)->nodeValue);
        $generalDataNode->appendChild($legalAddressNode);
        
        $addressForCorrespondenceNode = new DOMElement('addressForCorrespondence',$rawGeneralDataAboutUniversity->item(1)->nodeValue);
        $generalDataNode->appendChild($addressForCorrespondenceNode);
        
        $telephoneNumberWithAreaCodeNode = new DOMElement('telephoneNumberWithAreaCode',$rawGeneralDataAboutUniversity->item(2)->nodeValue);
        $generalDataNode->appendChild($telephoneNumberWithAreaCodeNode);
        
        $faxNode = new DOMElement('fax',$rawGeneralDataAboutUniversity->item(3)->nodeValue);
        $generalDataNode->appendChild($faxNode);    

        $emailAddressNode = new DOMElement('emailAddress',$rawGeneralDataAboutUniversity->item(4)->nodeValue);
        $generalDataNode->appendChild($emailAddressNode);
        
        $internetSiteNode = new DOMElement('internetSite',$rawGeneralDataAboutUniversity->item(5)->nodeValue);
        $generalDataNode->appendChild($internetSiteNode);
        
        $rectorNode = new DOMElement('rector',$rawGeneralDataAboutUniversity->item(6)->nodeValue);
        $generalDataNode->appendChild($rectorNode);
        
        $viceRectorForAccreditationNode = new DOMElement('viceRectorForAccreditation',$rawGeneralDataAboutUniversity->item(7)->nodeValue);
        $generalDataNode->appendChild($viceRectorForAccreditationNode);

        $INNNode = new DOMElement('INN',$rawGeneralDataAboutUniversity->item(8)->nodeValue);
        $generalDataNode->appendChild($INNNode);
        
        $OGRNNode = new DOMElement('OGRN',$rawGeneralDataAboutUniversity->item(9)->nodeValue);
        $generalDataNode->appendChild($OGRNNode);
        
        $requisitesAdministrativeOfDocumentsNode = new DOMElement('requisitesAdministrativeOfDocuments',$rawGeneralDataAboutUniversity->item(10)->nodeValue);
        $generalDataNode->appendChild($requisitesAdministrativeOfDocumentsNode);
        
        $detailsAndDurationOfCertificateNode = new DOMElement('detailsAndDurationOfCertificate',$rawGeneralDataAboutUniversity->item(11)->nodeValue);
        $generalDataNode->appendChild($detailsAndDurationOfCertificateNode);    

        $kindStateAccreditationStatusNode = new DOMElement('kindStateAccreditationStatus',$rawGeneralDataAboutUniversity->item(12)->nodeValue);
        $generalDataNode->appendChild($kindStateAccreditationStatusNode);
    
    }
    
    $xpathExpression = '/html/body/div/table/tbody/tr/td[2]/ol/li/a';
    $rawListOfBranches = $xpathObject->query($xpathExpression);

    if ($rawListOfBranches) {
        $listOfBranchesNode = $dom->appendChild(new DOMElement('listOfBranches'));
        for ($i = 0; $i < $rawListOfBranches->length; $i++) {
            $branchNode = new DOMElement('branch',$rawListOfBranches->item($i)->nodeValue);
            $listOfBranchesNode->appendChild($branchNode);          
        }
    }
    
    //Образовательная программа, направление подготовки (специальность), профессия
    $xpathExpression = '/html/body/div/table/tbody/tr/td[2]/table[@class="svid_spec"][1]/tr[@class="svid_spec_row"]';
    $rawListOfProfession = $xpathObject->query($xpathExpression);
    
    if ($rawListOfProfession) {
        $listOfProfessionNode = $dom->appendChild(new DOMElement('listOfProfession'));
        for ($i = 0; $i < $rawListOfProfession->length; $i++) {
            
            $td = $rawListOfProfession->item($i)->getElementsByTagName('td');

            $profession = new DOMElement('profession');
            $listOfProfessionNode->appendChild($profession);
            
            $kind = new DOMElement('kind',$td->item(0)->nodeValue);
            $profession->appendChild($kind);

            $code = new DOMElement('code',$td->item(1)->nodeValue);
            $profession->appendChild($code);
            
            $title = new DOMElement('title',$td->item(2)->nodeValue);
            $profession->appendChild($title);

            $levelOfEducation = new DOMElement('levelOfEducation',$td->item(3)->nodeValue);
            $profession->appendChild($levelOfEducation);

            $qualification = new DOMElement('qualification',$td->item(4)->nodeValue);
            $profession->appendChild($qualification);           
        }
    }
    
    //Укрупненные группы направлений подготовки и специальностей
    $xpathExpression = '/html/body/div/table/tbody/tr/td[2]/table[@class="svid_spec"][2]/tr[@class="svid_spec_row"]';
    $rawListOfSpecialties = $xpathObject->query($xpathExpression);  

    if ($rawListOfSpecialties) {
        $listOfSpecialtiesNode = $dom->appendChild(new DOMElement('listOfSpecialties'));
        for ($i = 0; $i < $rawListOfSpecialties->length; $i++) {
            
            $td = $rawListOfSpecialties->item($i)->getElementsByTagName('td');

            $specialty = new DOMElement('specialty');
            $listOfSpecialtiesNode->appendChild($specialty);
            
            $kind = new DOMElement('kind',$td->item(0)->nodeValue);
            $specialty->appendChild($kind);

            $code = new DOMElement('code',$td->item(1)->nodeValue);
            $specialty->appendChild($code);
            
            $title = new DOMElement('title',$td->item(2)->nodeValue);
            $specialty->appendChild($title);

            $levelOfEducation = new DOMElement('levelOfEducation',$td->item(3)->nodeValue);
            $specialty->appendChild($levelOfEducation);         
        }
    }   
    
    //Группы специальностей аспирантуры
    $xpathExpression = '/html/body/div/table/tbody/tr/td[2]/table[@class="svid_spec"][3]/tr[@class="svid_spec_row"]';
    $rawListOfPostgraduate = $xpathObject->query($xpathExpression);
    
    if ($rawListOfPostgraduate) {
        $listOfPostgraduateNode = $dom->appendChild(new DOMElement('listOfPostgraduate'));
        for ($i = 0; $i < $rawListOfPostgraduate->length; $i++) {
            
            $td = $rawListOfPostgraduate->item($i)->getElementsByTagName('td');

            $postgraduate = new DOMElement('postgraduate');
            $listOfPostgraduateNode->appendChild($postgraduate);
            
            $kind = new DOMElement('kind',$td->item(0)->nodeValue);
            $postgraduate->appendChild($kind);

            $code = new DOMElement('code',$td->item(1)->nodeValue);
            $postgraduate->appendChild($code);
            
            $title = new DOMElement('title',$td->item(2)->nodeValue);
            $postgraduate->appendChild($title);

            $levelOfEducation = new DOMElement('qualification',$td->item(3)->nodeValue);
            $postgraduate->appendChild($levelOfEducation);  
            
        }
    }
    return $dom;
}
?>
