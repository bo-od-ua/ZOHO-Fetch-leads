<?php
/*
Plugin Name: ZOHO Fetch leads ы
Description: Fetch Records from the "Leads" Module.*.
Version: 1.0
*/

define("ZOHO_COMPANY", 'company');
define("ZOHO_DEF_AFFILIATE_ID", '11111');

add_action('show_user_profile', 'my_profile_zohoauthtoken_field_add');
add_action('edit_user_profile', 'my_profile_zohoauthtoken_field_add');

add_action('personal_options_update', 'my_profile_zohoauthtoken_field_update');
add_action('edit_user_profile_update', 'my_profile_zohoauthtoken_field_update');

function my_profile_zohoauthtoken_field_add(){
	global $user_ID;
	global $profileuser;

	$zohoauthtoken= get_user_meta( $profileuser->id, "affiliate_id", 1 );

	?>
	<table class="form-table">
		<tr>
			<th>Affiliate ID</th>
			<td>
				<input class="regular-text ltr" type="text" name="affiliate_id" value="<?php echo $zohoauthtoken ?>"><br>
			</td>
		</tr>
	</table>
	<?php
}

function my_profile_zohoauthtoken_field_update(){
	global $user_ID;

	update_user_meta($_POST['user_id'], "affiliate_id", $_POST['affiliate_id']);
}

function zoho_fetch_leads_shortcode( $atts ){
	global $user_ID;
	$Content=    '';
	$CopyInvait= '';

	$Affiliate_ID= trim(get_user_meta($user_ID, "affiliate_id", 1));

	if($Affiliate_ID)
	{
		$nd= '?';
		$URL= get_permalink();
		if(strpos($URL, '?')!== false) $nd= '&';

		$CopyInvait.= '
			&nbsp;or&nbsp;&nbsp;<input id="copy_invite" type="button" value="copy invite">
			<div style="padding-top:20px;"><input type="text" id="invite_link" value="'.$URL.$nd.'invite=3red0hgr52y&Affiliate_ID='.$Affiliate_ID.'"></div>
		';
		$Content.= '<div class="newlead"><a id="new_lead_trigger" href="#" style="margin-right:20px;"><b>new lead</b></a>';
		$Content.= '<a id="import_leads_trigger" href="#"><b>import leads</b></a>';
		$Content.= '<div id="import_leads_wrapper" style="display:none;">';
		$Content.= add_import_leads_form($Affiliate_ID, $CopyInvait).'</div>';
		$Content.= '<div id="new_lead_wrapper" style="display:none;">';
		$Content.= add_lead_form($Affiliate_ID, $CopyInvait).'</div></div>';
		$Content.= '
		<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">
		<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/rowreorder/1.2.5/css/rowReorder.dataTables.min.css">
		<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/responsive/2.2.3/css/responsive.dataTables.min.css">
		<script src="//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
		<script src="//cdn.datatables.net/rowreorder/1.2.5/js/dataTables.rowReorder.min.js"></script>
		<script src="//cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js"></script>
		<script>
		jQuery(function($) {
			$(document).ready(function(){
				$("#zoho_leads").DataTable({
					"pagingType": "full_numbers",
					"processing": true,
					"serverSide": true,
					"pageLength": 50,
					rowReorder: {
	            selector: \'td:nth-child(2)\'
	        },
	        responsive: true,
						"ajax": "/wp-admin/admin-ajax.php?action=zoho_get&Affiliate_ID='.$Affiliate_ID.'",
				});
			});

			$(document).on("click", "#new_lead_trigger", function(i, e){
				$("#new_lead_wrapper").toggle("slow");
			return false;
			});

			$(document).on("click", "#import_leads_trigger", function(i, e){
				$("#import_leads_wrapper").toggle("slow");
			return false;
			});

			$(document).on("click", "#copy_invite", function(i, e){
				$("#copy_invite").hide("slow");
				$("#invite_link").select();
				document.execCommand("copy");
				setTimeout(function(){$("#copy_invite").show("slow");}, 1000);
			});
		});
		</script>';
		$Content.= '<div class="table"><table id="zoho_leads" class="display nowrap" style="width:100%" data-t="'.$Affiliate_ID.'">
			<thead>
				<tr>
					<th class="id">ID</th>
					<th class="name">Full Name</th>
					<th class="email">Email</th>
					<th class="phone">Phone</th>
					<th class="tag">Tag</th>
					<th class="deposit">Deposited Amount</th>
					<th class="country">Country</th>
					<th class="date">Date</th>
				</tr>
			</thead>
			<tbody>'."\n";
		$Content.= '</tfoot></table></div>';
	}
	else
	{
		if(($_REQUEST['invite']== '3red0hgr52y') && $_REQUEST['Affiliate_ID'])
		{
			$Content= add_lead_form($_REQUEST['Affiliate_ID']);
		}
		else
		{
			$Content= add_lead_form_4guest();
		}
	}

	return $Content;
}
add_shortcode('zoho_fetch_leads', 'zoho_fetch_leads_shortcode');

function add_lead_form($Affiliate_ID, $CopyInvait= '')
{
	$Content= '
	<script>
	jQuery(function($) {
		$(document).on("click", "#zoho_lead_add_submit", function(i, e){

			$("#zoho_lead_add input[type=text]").each(function(i, e) {
				if(($(e).attr("required")== "required") && !$(e).val().length)
				{
					alert("required fields must be filled.");
					return false;
				}
			});
		});
	});
	</script>
	<style type="text/css">
		#zoho_lead_add{padding-bottom:20px;}
		#zoho_lead_add div{padding-bottom:10px; width:100%;}
		#zoho_lead_add div input{width:70%;}
		#zoho_lead_add div .required{color: #f3615a;}
	</style>
	<form id="zoho_lead_add" action="/wp-admin/admin-ajax.php" method="post">
		<div><input type="text" name="Full_Name" placeholder="Full Name" required="required"><span class="required">*</span></div>
		<div><input type="text" name="Email" placeholder="Email"></div>
		<div><input type="text" name="Phone" placeholder="Phone"></div>
		<div><input type="text" name="Country_text" placeholder="Country"></div>
		<div><span class="required">*</span> - required</div>
		<input type="hidden" name="action" value="zoho_set">
		<input type="hidden" name="Affiliate_ID" value="'.$Affiliate_ID.'">
		<input id="zoho_lead_add_submit" type="submit" value="add">';
	if($CopyInvait) $Content.= $CopyInvait;
	$Content.= '</form>';

return $Content;
}

function add_lead_form_4guest()
{
	$Content= '
	<script>
	jQuery(function($) {
		$(document).on("click", "#zoho_lead_add_submit", function(i, e){

			$("#zoho_lead_add input[type=text]").each(function(i, e) {
				if(($(e).attr("required")== "required") && !$(e).val().length)
				{
					alert("required fields must be filled.");
					return false;
				}
			});
		});
	});
	</script>
	<style type="text/css">
		#zoho_lead_add{padding-bottom:20px;}
		#zoho_lead_add div{padding-bottom:10px; width:100%;}
		#zoho_lead_add div input{width:70%;}
		#zoho_lead_add div .required{color: #f3615a;}
	</style>
	<form id="zoho_lead_add" action="/wp-admin/admin-ajax.php" method="post">
		<div><input type="text" name="Full_Name" placeholder="Full Name" required="required"><span class="required">*</span></div>
		<div><input type="text" name="Email" placeholder="Email"></div>
		<div><input type="text" name="Phone" placeholder="Phone"></div>
		<div><input type="text" name="Country_text" placeholder="Country"></div>
		<div><span class="required">*</span> - required</div>
		<input type="hidden" name="action" value="zoho_set">
		<input type="hidden" name="Affiliate_ID" value="'.ZOHO_DEF_AFFILIATE_ID.'">
		<input id="zoho_lead_add_submit" type="submit" value="register">';
	if($CopyInvait) $Content.= $CopyInvait;
	$Content.= '</form>';

return $Content;
}

function add_import_leads_form($Affiliate_ID, $CopyInvait= '')
{
	$Content= '
	<style type="text/css">
		#zoho_import_leads{padding-bottom:20px;}
		#zoho_import_leads div{padding-bottom:10px; width:100%;}
		#zoho_import_leads div textarea{width:70%;height:200px;}
	</style>
	<form id="zoho_import_leads" action="/wp-admin/admin-ajax.php" method="post">
		<div><textarea name="txt"></textarea></div>
		<input type="hidden" name="action" value="zoho_set">
		<input type="hidden" name="import" value="1">
		<input type="hidden" name="Affiliate_ID" value="'.$Affiliate_ID.'">
		<input id="zoho_import_leads_submit" type="submit" value="import">';
	$Content.= '</form>';

return $Content;
}

add_action( 'wp_ajax_zoho_get', 'zoho_get' );
function zoho_get() {

	$_REQUEST['action']= 'get';
	$_REQUEST['company']= ZOHO_COMPANY;

	$RecordsTotal= 30000;
	$RecordsPP=    50;
	$RecordFrom=   1;
	$Affiliate_ID= $_REQUEST['Affiliate_ID'];

	if($_REQUEST['RecordFrom']) $RecordFrom= (int)$_REQUEST['RecordFrom'];
	if($_REQUEST['length'])     $RecordsPP=  (int)$_REQUEST['length'];
	if($_REQUEST['start'])      $RecordFrom= (int)$_REQUEST['start'];

	$Criteria= "(Affiliate ID:".$Affiliate_ID.")";
	if(strlen($_REQUEST['search']['value']))
	{
		$Search= '(Email:'.$_REQUEST['search']['value'].')';
		$Criteria= '('.$Criteria.'AND'.$Search.')';
	}
	$sReply= zoho_search_records($Criteria, 'Contacts', $RecordFrom, $RecordsPP);
	$sRez= $sReply['response']['result']['Contacts']['row'];

	$i= 0;
	$sData['data']= [];
	$sData["iTotalRecords"]= 0;
	$sData["iTotalDisplayRecords"]= 0;

	if(!empty($sRez['FL']))
	{
		$sData['data'][$i]= row2Datatable($sRez['FL'], $Affiliate_ID);
	}
	else
	{
		foreach($sRez as $K=> $V)
		{
			$hRow= [];
			$sData['data'][$i]= row2Datatable($V['FL'], $Affiliate_ID);
			if(!empty($sData['data'][$i])) $i++;
		}
	}

	if($i)
	{
		$sData["iTotalRecords"]= 300000;
		$sData["iTotalDisplayRecords"]= 300000;
	}

	print json_encode($sData);

	wp_die();
}

private static function row2Datatable($sInData, $Affiliate_ID)
{
	$hRow= [];
	foreach($sInData as $K=> $V)
	{
		$hRow[$V['val']]= $V['content'];
	}
	if($Affiliate_ID== $hRow['Affiliate ID'])
	{
		$sData[0]= $hRow['Affiliate ID'];
		$sData[1]= $hRow['Full Name'];
		$sData[2]= $hRow['Email'];
		$sData[3]= $hRow['Phone'];
		$sData[4]= $hRow['Tag'];
		$sData[5]= $hRow['Deposited Amount'];
		$sData[6]= $hRow['Country'];
		$sData[7]= $hRow['Created Time'];
		$i++;
	}
return($sData);
}

private static function zoho_search_records($Criteria= '', $Category= 'Contacts', $RecordFrom= 1, $RecordsPP= 50)
{
	global $user_ID;
	$Authtoken= trim(get_user_meta($user_ID, "zohoauthtoken", 1));
	$hColumns= array(
		0=> 'Affiliate ID',
		1=> 'Full Name',
		2=> 'Email',
		3=> 'Phone',
		4=> 'Tag',
		5=> 'Deposited Amount',
		6=> 'Country',
		7=> 'Created Time',
	);
	$url= "https://crm.zoho.eu/crm/private/json/".$Category."/searchRecords";
	$param= "authtoken=".$Authtoken."&scope=crmapi";
	if($Criteria) $param.= '&criteria='.$Criteria;
	$param.= "&fromIndex=".$RecordFrom."&toIndex=".($RecordFrom+ $RecordsPP- 1);

	if(strlen($_REQUEST['order'][0]['dir']))
	{
		$c= $_REQUEST['order'][0]['column'];
		$param.= '&sortColumnString='.$hColumns[$c].'&sortOrderString='.$_REQUEST['order'][0]['dir'];
	}

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
	$result = curl_exec($ch);
	curl_close($ch);

return(json_decode($result, true));
}

add_action('wp_ajax_zoho_set', 'zoho_set');
add_action('wp_ajax_nopriv_zoho_set', 'zoho_set');
function zoho_set() {
	$_REQUEST['action']= 'set';
	$_REQUEST['company']= ZOHO_COMPANY;

	$url= 'https://crm.zoho.eu/crm/private/xml/Contacts/insertRecords';
	if($_REQUEST['Affiliate_ID'])
	{
		if($_REQUEST['import'])
		{
			$sList= zoho_txt_parse($_REQUEST['txt']);
		}
		else
		{
			if($_REQUEST['Full_Name'])
			{
				$sList[]= $_REQUEST;
			}
		}
		if(count($sList)){ print zoho_insert($url, zoho_to_xml($sList, ZOHO_COMPANY));}
		else{ print 'set err.';}
	}

	print '<script type="text/javascript">
	setTimeout(function(){location="'.$_SERVER['HTTP_REFERER'].'"}, 1000);
</script>

<br><a href="'.$_SERVER['HTTP_REFERER'].'">done.</a>';
	wp_die();
}

zoho_to_xml($sList, $Company= '', $Section='Contacts')
{
	$Countries= '{"AFGHANISTAN":{"name":"Afghanistan","dial_code":"+93","code":"AF","latitude":33,"longitude":65},"ALBANIA":{"name":"Albania","dial_code":"+355","code":"AL","latitude":41,"longitude":20},"ALGERIA":{"name":"Algeria","dial_code":"+213","code":"DZ","latitude":28,"longitude":3},"AMERICANSAMOA":{"name":"AmericanSamoa","dial_code":"+1 684","code":"AS","latitude":-14.3333,"longitude":-170},"ANDORRA":{"name":"Andorra","dial_code":"+376","code":"AD","latitude":42.5,"longitude":1.6},"ANGOLA":{"name":"Angola","dial_code":"+244","code":"AO","latitude":-12.5,"longitude":18.5},"ANGUILLA":{"name":"Anguilla","dial_code":"+1 264","code":"AI","latitude":18.25,"longitude":-63.1667},"ANTARCTICA":{"name":"Antarctica","dial_code":"+672","code":"AQ","latitude":-90,"longitude":"0"},"ANTIGUA AND BARBUDA":{"name":"Antigua and Barbuda","dial_code":"+1268","code":"AG","latitude":17.05,"longitude":-61.8},"ARGENTINA":{"name":"Argentina","dial_code":"+54","code":"AR","latitude":-34,"longitude":-64},"ARMENIA":{"name":"Armenia","dial_code":"+374","code":"AM","latitude":40,"longitude":45},"ARUBA":{"name":"Aruba","dial_code":"+297","code":"AW","latitude":12.5,"longitude":-69.9667},"AUSTRALIA":{"name":"Australia","dial_code":"+61","code":"AU","latitude":-27,"longitude":133},"AUSTRIA":{"name":"Austria","dial_code":"+43","code":"AT","latitude":47.3333,"longitude":13.3333},"AZERBAIJAN":{"name":"Azerbaijan","dial_code":"+994","code":"AZ","latitude":40.5,"longitude":47.5},"BAHAMAS":{"name":"Bahamas","dial_code":"+1 242","code":"BS","latitude":24.25,"longitude":-76},"BAHRAIN":{"name":"Bahrain","dial_code":"+973","code":"BH","latitude":26,"longitude":50.55},"BANGLADESH":{"name":"Bangladesh","dial_code":"+880","code":"BD","latitude":24,"longitude":90},"BARBADOS":{"name":"Barbados","dial_code":"+1 246","code":"BB","latitude":13.1667,"longitude":-59.5333},"BELARUS":{"name":"Belarus","dial_code":"+375","code":"BY","latitude":53,"longitude":28},"BELGIUM":{"name":"Belgium","dial_code":"+32","code":"BE","latitude":50.8333,"longitude":4},"BELIZE":{"name":"Belize","dial_code":"+501","code":"BZ","latitude":17.25,"longitude":-88.75},"BENIN":{"name":"Benin","dial_code":"+229","code":"BJ","latitude":9.5,"longitude":2.25},"BERMUDA":{"name":"Bermuda","dial_code":"+1 441","code":"BM","latitude":32.3333,"longitude":-64.75},"BHUTAN":{"name":"Bhutan","dial_code":"+975","code":"BT","latitude":27.5,"longitude":90.5},"BOLIVIA, PLURINATIONAL STATE OF BOLIVIA":{"name":"Bolivia, Plurinational State of Bolivia","dial_code":"+591","code":"BO","latitude":-17,"longitude":-65},"BOSNIA AND HERZEGOVINA":{"name":"Bosnia and Herzegovina","dial_code":"+387","code":"BA","latitude":44,"longitude":18},"BOTSWANA":{"name":"Botswana","dial_code":"+267","code":"BW","latitude":-22,"longitude":24},"BOUVET ISLAND":{"name":"Bouvet Island","dial_code":"+55","code":"BV","latitude":-54.4333,"longitude":3.4},"BRAZIL":{"name":"Brazil","dial_code":"+55","code":"BR","latitude":-10,"longitude":-55},"BRITISH INDIAN OCEAN TERRITORY":{"name":"British Indian Ocean Territory","dial_code":"+246","code":"IO","latitude":-6,"longitude":71.5},"BRUNEI DARUSSALAM":{"name":"Brunei Darussalam","dial_code":"+673","code":"BN","latitude":4.5,"longitude":114.6667},"BULGARIA":{"name":"Bulgaria","dial_code":"+359","code":"BG","latitude":43,"longitude":25},"BURKINA FASO":{"name":"Burkina Faso","dial_code":"+226","code":"BF","latitude":13,"longitude":-2},"BURUNDI":{"name":"Burundi","dial_code":"+257","code":"BI","latitude":-3.5,"longitude":30},"CAMBODIA":{"name":"Cambodia","dial_code":"+855","code":"KH","latitude":13,"longitude":105},"CAMEROON":{"name":"Cameroon","dial_code":"+237","code":"CM","latitude":6,"longitude":12},"CANADA":{"name":"Canada","dial_code":"+1","code":"CA","latitude":60,"longitude":-95},"CAPE VERDE":{"name":"Cape Verde","dial_code":"+238","code":"CV","latitude":16,"longitude":-24},"CAYMAN ISLANDS":{"name":"Cayman Islands","dial_code":"+1345","code":"KY","latitude":19.5,"longitude":-80.5},"CENTRAL AFRICAN REPUBLIC":{"name":"Central African Republic","dial_code":"+236","code":"CF","latitude":7,"longitude":21},"CHAD":{"name":"Chad","dial_code":"+235","code":"TD","latitude":15,"longitude":19},"CHILE":{"name":"Chile","dial_code":"+56","code":"CL","latitude":-30,"longitude":-71},"CHINA":{"name":"China","dial_code":"+86","code":"CN","latitude":35,"longitude":105},"CHRISTMAS ISLAND":{"name":"Christmas Island","dial_code":"+61","code":"CX","latitude":-10.5,"longitude":105.6667},"COCOS (KEELING) ISLANDS":{"name":"Cocos (Keeling) Islands","dial_code":"+61","code":"CC","latitude":-12.5,"longitude":96.8333},"COLOMBIA":{"name":"Colombia","dial_code":"+57","code":"CO","latitude":4,"longitude":-72},"COMOROS":{"name":"Comoros","dial_code":"+269","code":"KM","latitude":-12.1667,"longitude":44.25},"CONGO":{"name":"Congo","dial_code":"+242","code":"CG","latitude":-1,"longitude":15},"CONGO, THE DEMOCRATIC REPUBLIC OF THE":{"name":"Congo, The Democratic Republic of the","dial_code":"+243","code":"CD","latitude":"0","longitude":25},"COOK ISLANDS":{"name":"Cook Islands","dial_code":"+682","code":"CK","latitude":-21.2333,"longitude":-159.7667},"COSTA RICA":{"name":"Costa Rica","dial_code":"+506","code":"CR","latitude":10,"longitude":-84},"COTE D\'IVOIRE":{"name":"Cote d\'Ivoire","dial_code":"+225","code":"CI","latitude":8,"longitude":-5},"CROATIA":{"name":"Croatia","dial_code":"+385","code":"HR","latitude":45.1667,"longitude":15.5},"CUBA":{"name":"Cuba","dial_code":"+53","code":"CU","latitude":21.5,"longitude":-80},"CYPRUS":{"name":"Cyprus","dial_code":"+357","code":"CY","latitude":35,"longitude":33},"CZECH REPUBLIC":{"name":"Czech Republic","dial_code":"+420","code":"CZ","latitude":49.75,"longitude":15.5},"DENMARK":{"name":"Denmark","dial_code":"+45","code":"DK","latitude":56,"longitude":10},"DJIBOUTI":{"name":"Djibouti","dial_code":"+253","code":"DJ","latitude":11.5,"longitude":43},"DOMINICA":{"name":"Dominica","dial_code":"+1 767","code":"DM","latitude":15.4167,"longitude":-61.3333},"DOMINICAN REPUBLIC":{"name":"Dominican Republic","dial_code":"+1 849","code":"DO","latitude":19,"longitude":-70.6667},"ECUADOR":{"name":"Ecuador","dial_code":"+593","code":"EC","latitude":-2,"longitude":-77.5},"EGYPT":{"name":"Egypt","dial_code":"+20","code":"EG","latitude":27,"longitude":30},"EL SALVADOR":{"name":"El Salvador","dial_code":"+503","code":"SV","latitude":13.8333,"longitude":-88.9167},"EQUATORIAL GUINEA":{"name":"Equatorial Guinea","dial_code":"+240","code":"GQ","latitude":2,"longitude":10},"ERITREA":{"name":"Eritrea","dial_code":"+291","code":"ER","latitude":15,"longitude":39},"ESTONIA":{"name":"Estonia","dial_code":"+372","code":"EE","latitude":59,"longitude":26},"ETHIOPIA":{"name":"Ethiopia","dial_code":"+251","code":"ET","latitude":8,"longitude":38},"FALKLAND ISLANDS (MALVINAS)":{"name":"Falkland Islands (Malvinas)","dial_code":"+500","code":"FK","latitude":-51.75,"longitude":-59},"FAROE ISLANDS":{"name":"Faroe Islands","dial_code":"+298","code":"FO","latitude":62,"longitude":-7},"FIJI":{"name":"Fiji","dial_code":"+679","code":"FJ","latitude":-18,"longitude":175},"FINLAND":{"name":"Finland","dial_code":"+358","code":"FI","latitude":64,"longitude":26},"FRANCE":{"name":"France","dial_code":"+33","code":"FR","latitude":46,"longitude":2},"FRENCH GUIANA":{"name":"French Guiana","dial_code":"+594","code":"GF","latitude":4,"longitude":-53},"FRENCH POLYNESIA":{"name":"French Polynesia","dial_code":"+689","code":"PF","latitude":-15,"longitude":-140},"FRENCH SOUTHERN AND ANTARCTIC LANDS":{"name":"French Southern and Antarctic Lands","dial_code":"+262","code":"TF","latitude":-43,"longitude":67},"GABON":{"name":"Gabon","dial_code":"+241","code":"GA","latitude":-1,"longitude":11.75},"GAMBIA":{"name":"Gambia","dial_code":"+220","code":"GM","latitude":13.4667,"longitude":-16.5667},"GEORGIA":{"name":"Georgia","dial_code":"+995","code":"GE","latitude":42,"longitude":43.5},"GERMANY":{"name":"Germany","dial_code":"+49","code":"DE","latitude":51,"longitude":9},"GHANA":{"name":"Ghana","dial_code":"+233","code":"GH","latitude":8,"longitude":-2},"GIBRALTAR":{"name":"Gibraltar","dial_code":"+350","code":"GI","latitude":36.1833,"longitude":-5.3667},"GREECE":{"name":"Greece","dial_code":"+30","code":"GR","latitude":39,"longitude":22},"GREENLAND":{"name":"Greenland","dial_code":"+299","code":"GL","latitude":72,"longitude":-40},"GRENADA":{"name":"Grenada","dial_code":"+1 473","code":"GD","latitude":12.1167,"longitude":-61.6667},"GUADELOUPE":{"name":"Guadeloupe","dial_code":"+590","code":"GP","latitude":16.25,"longitude":-61.5833},"GUAM":{"name":"Guam","dial_code":"+1 671","code":"GU","latitude":13.4667,"longitude":144.7833},"GUATEMALA":{"name":"Guatemala","dial_code":"+502","code":"GT","latitude":15.5,"longitude":-90.25},"GUERNSEY":{"name":"Guernsey","dial_code":"+44","code":"GG","latitude":49.5,"longitude":-2.56},"GUINEA":{"name":"Guinea","dial_code":"+224","code":"GN","latitude":11,"longitude":-10},"GUINEA-BISSAU":{"name":"Guinea-Bissau","dial_code":"+245","code":"GW","latitude":12,"longitude":-15},"GUYANA":{"name":"Guyana","dial_code":"+592","code":"GY","latitude":5,"longitude":-59},"HAITI":{"name":"Haiti","dial_code":"+509","code":"HT","latitude":19,"longitude":-72.4167},"HEARD ISLAND AND MCDONALD ISLANDS":{"name":"Heard Island and McDonald Islands","dial_code":"+672","code":"HM","latitude":-53.1,"longitude":72.5167},"HOLY SEE (VATICAN CITY STATE)":{"name":"Holy See (Vatican City State)","dial_code":"+379","code":"VA","latitude":41.9,"longitude":12.45},"HONDURAS":{"name":"Honduras","dial_code":"+504","code":"HN","latitude":15,"longitude":-86.5},"HONG KONG":{"name":"Hong Kong","dial_code":"+852","code":"HK","latitude":22.25,"longitude":114.1667},"HUNGARY":{"name":"Hungary","dial_code":"+36","code":"HU","latitude":47,"longitude":20},"ICELAND":{"name":"Iceland","dial_code":"+354","code":"IS","latitude":65,"longitude":-18},"INDIA":{"name":"India","dial_code":"+91","code":"IN","latitude":20,"longitude":77},"INDONESIA":{"name":"Indonesia","dial_code":"+62","code":"ID","latitude":-5,"longitude":120},"IRAN, ISLAMIC REPUBLIC OF":{"name":"Iran, Islamic Republic of","dial_code":"+98","code":"IR","latitude":32,"longitude":53},"IRAQ":{"name":"Iraq","dial_code":"+964","code":"IQ","latitude":33,"longitude":44},"IRELAND":{"name":"Ireland","dial_code":"+353","code":"IE","latitude":53,"longitude":-8},"ISLE OF MAN":{"name":"Isle of Man","dial_code":"+44","code":"IM","latitude":54.23,"longitude":-4.55},"ISRAEL":{"name":"Israel","dial_code":"+972","code":"IL","latitude":31.5,"longitude":34.75},"ITALY":{"name":"Italy","dial_code":"+39","code":"IT","latitude":42.8333,"longitude":12.8333},"JAMAICA":{"name":"Jamaica","dial_code":"+1 876","code":"JM","latitude":18.25,"longitude":-77.5},"JAPAN":{"name":"Japan","dial_code":"+81","code":"JP","latitude":36,"longitude":138},"JERSEY":{"name":"Jersey","dial_code":"+44","code":"JE","latitude":49.21,"longitude":-2.13},"JORDAN":{"name":"Jordan","dial_code":"+962","code":"JO","latitude":31,"longitude":36},"KAZAKHSTAN":{"name":"Kazakhstan","dial_code":"+7","code":"KZ","latitude":48,"longitude":68},"KENYA":{"name":"Kenya","dial_code":"+254","code":"KE","latitude":1,"longitude":38},"KIRIBATI":{"name":"Kiribati","dial_code":"+686","code":"KI","latitude":1.4167,"longitude":173},"KOREA, DEMOCRATIC PEOPLE\'S REPUBLIC OF":{"name":"Korea, Democratic People\'s Republic of","dial_code":"+850","code":"KP","latitude":40,"longitude":127},"KOREA, REPUBLIC OF":{"name":"Korea, Republic of","dial_code":"+82","code":"KR","latitude":37,"longitude":127.5},"KUWAIT":{"name":"Kuwait","dial_code":"+965","code":"KW","latitude":29.3375,"longitude":47.6581},"KYRGYZSTAN":{"name":"Kyrgyzstan","dial_code":"+996","code":"KG","latitude":41,"longitude":75},"LAO PEOPLE\'S DEMOCRATIC REPUBLIC":{"name":"Lao People\'s Democratic Republic","dial_code":"+856","code":"LA","latitude":18,"longitude":105},"LATVIA":{"name":"Latvia","dial_code":"+371","code":"LV","latitude":57,"longitude":25},"LEBANON":{"name":"Lebanon","dial_code":"+961","code":"LB","latitude":33.8333,"longitude":35.8333},"LESOTHO":{"name":"Lesotho","dial_code":"+266","code":"LS","latitude":-29.5,"longitude":28.5},"LIBERIA":{"name":"Liberia","dial_code":"+231","code":"LR","latitude":6.5,"longitude":-9.5},"LIBYAN ARAB JAMAHIRIYA":{"name":"Libyan Arab Jamahiriya","dial_code":"+218","code":"LY","latitude":25,"longitude":17},"LIECHTENSTEIN":{"name":"Liechtenstein","dial_code":"+423","code":"LI","latitude":47.1667,"longitude":9.5333},"LITHUANIA":{"name":"Lithuania","dial_code":"+370","code":"LT","latitude":56,"longitude":24},"LUXEMBOURG":{"name":"Luxembourg","dial_code":"+352","code":"LU","latitude":49.75,"longitude":6.1667},"MACAO":{"name":"Macao","dial_code":"+853","code":"MO","latitude":22.1667,"longitude":113.55},"MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF":{"name":"Macedonia, The Former Yugoslav Republic of","dial_code":"+389","code":"MK","latitude":41.8333,"longitude":22},"MADAGASCAR":{"name":"Madagascar","dial_code":"+261","code":"MG","latitude":-20,"longitude":47},"MALAWI":{"name":"Malawi","dial_code":"+265","code":"MW","latitude":-13.5,"longitude":34},"MALAYSIA":{"name":"Malaysia","dial_code":"+60","code":"MY","latitude":2.5,"longitude":112.5},"MALDIVES":{"name":"Maldives","dial_code":"+960","code":"MV","latitude":3.25,"longitude":73},"MALI":{"name":"Mali","dial_code":"+223","code":"ML","latitude":17,"longitude":-4},"MALTA":{"name":"Malta","dial_code":"+356","code":"MT","latitude":35.8333,"longitude":14.5833},"MARSHALL ISLANDS":{"name":"Marshall Islands","dial_code":"+692","code":"MH","latitude":9,"longitude":168},"MARTINIQUE":{"name":"Martinique","dial_code":"+596","code":"MQ","latitude":14.6667,"longitude":-61},"MAURITANIA":{"name":"Mauritania","dial_code":"+222","code":"MR","latitude":20,"longitude":-12},"MAURITIUS":{"name":"Mauritius","dial_code":"+230","code":"MU","latitude":-20.2833,"longitude":57.55},"MAYOTTE":{"name":"Mayotte","dial_code":"+262","code":"YT","latitude":-12.8333,"longitude":45.1667},"MEXICO":{"name":"Mexico","dial_code":"+52","code":"MX","latitude":23,"longitude":-102},"MICRONESIA, FEDERATED STATES OF":{"name":"Micronesia, Federated States of","dial_code":"+691","code":"FM","latitude":6.9167,"longitude":158.25},"MOLDOVA, REPUBLIC OF":{"name":"Moldova, Republic of","dial_code":"+373","code":"MD","latitude":47,"longitude":29},"MONACO":{"name":"Monaco","dial_code":"+377","code":"MC","latitude":43.7333,"longitude":7.4},"MONGOLIA":{"name":"Mongolia","dial_code":"+976","code":"MN","latitude":46,"longitude":105},"MONTENEGRO":{"name":"Montenegro","dial_code":"+382","code":"ME","latitude":42,"longitude":19},"MONTSERRAT":{"name":"Montserrat","dial_code":"+1664","code":"MS","latitude":16.75,"longitude":-62.2},"MOROCCO":{"name":"Morocco","dial_code":"+212","code":"MA","latitude":32,"longitude":-5},"MOZAMBIQUE":{"name":"Mozambique","dial_code":"+258","code":"MZ","latitude":-18.25,"longitude":35},"MYANMAR":{"name":"Myanmar","dial_code":"+95","code":"MM","latitude":22,"longitude":98},"NAMIBIA":{"name":"Namibia","dial_code":"+264","code":"NA","latitude":-22,"longitude":17},"NAURU":{"name":"Nauru","dial_code":"+674","code":"NR","latitude":-0.5333,"longitude":166.9167},"NEPAL":{"name":"Nepal","dial_code":"+977","code":"NP","latitude":28,"longitude":84},"NETHERLANDS":{"name":"Netherlands","dial_code":"+31","code":"NL","latitude":52.5,"longitude":5.75},"NETHERLANDS ANTILLES":{"name":"Netherlands Antilles","dial_code":"+599","code":"AN","latitude":12.25,"longitude":-68.75},"NEW CALEDONIA":{"name":"New Caledonia","dial_code":"+687","code":"NC","latitude":-21.5,"longitude":165.5},"NEW ZEALAND":{"name":"New Zealand","dial_code":"+64","code":"NZ","latitude":-41,"longitude":174},"NICARAGUA":{"name":"Nicaragua","dial_code":"+505","code":"NI","latitude":13,"longitude":-85},"NIGER":{"name":"Niger","dial_code":"+227","code":"NE","latitude":16,"longitude":8},"NIGERIA":{"name":"Nigeria","dial_code":"+234","code":"NG","latitude":10,"longitude":8},"NIUE":{"name":"Niue","dial_code":"+683","code":"NU","latitude":-19.0333,"longitude":-169.8667},"NORFOLK ISLAND":{"name":"Norfolk Island","dial_code":"+672","code":"NF","latitude":-29.0333,"longitude":167.95},"NORTHERN MARIANA ISLANDS":{"name":"Northern Mariana Islands","dial_code":"+1 670","code":"MP","latitude":15.2,"longitude":145.75},"NORWAY":{"name":"Norway","dial_code":"+47","code":"NO","latitude":62,"longitude":10},"OMAN":{"name":"Oman","dial_code":"+968","code":"OM","latitude":21,"longitude":57},"PAKISTAN":{"name":"Pakistan","dial_code":"+92","code":"PK","latitude":30,"longitude":70},"PALAU":{"name":"Palau","dial_code":"+680","code":"PW","latitude":7.5,"longitude":134.5},"PALESTINIAN TERRITORY, OCCUPIED":{"name":"Palestinian Territory, Occupied","dial_code":"+970","code":"PS","latitude":32,"longitude":35.25},"PANAMA":{"name":"Panama","dial_code":"+507","code":"PA","latitude":9,"longitude":-80},"PAPUA NEW GUINEA":{"name":"Papua New Guinea","dial_code":"+675","code":"PG","latitude":-6,"longitude":147},"PARAGUAY":{"name":"Paraguay","dial_code":"+595","code":"PY","latitude":-23,"longitude":-58},"PERU":{"name":"Peru","dial_code":"+51","code":"PE","latitude":-10,"longitude":-76},"PHILIPPINES":{"name":"Philippines","dial_code":"+63","code":"PH","latitude":13,"longitude":122},"PITCAIRN":{"name":"Pitcairn","dial_code":"+870","code":"PN","latitude":-24.7,"longitude":-127.4},"POLAND":{"name":"Poland","dial_code":"+48","code":"PL","latitude":52,"longitude":20},"PORTUGAL":{"name":"Portugal","dial_code":"+351","code":"PT","latitude":39.5,"longitude":-8},"PUERTO RICO":{"name":"Puerto Rico","dial_code":"+1 939","code":"PR","latitude":18.25,"longitude":-66.5},"QATAR":{"name":"Qatar","dial_code":"+974","code":"QA","latitude":25.5,"longitude":51.25},"REUNION":{"name":"Reunion","dial_code":"+262","code":"RE","latitude":-21.1,"longitude":55.6},"ROMANIA":{"name":"Romania","dial_code":"+40","code":"RO","latitude":46,"longitude":25},"RUSSIA":{"name":"Russia","dial_code":"+7","code":"RU","latitude":60,"longitude":100},"RWANDA":{"name":"Rwanda","dial_code":"+250","code":"RW","latitude":-2,"longitude":30},"SAINT HELENA, ASCENSION AND TRISTAN DA CUNHA":{"name":"Saint Helena, Ascension and Tristan Da Cunha","dial_code":"+290","code":"SH","latitude":-15.9333,"longitude":-5.7},"SAINT KITTS AND NEVIS":{"name":"Saint Kitts and Nevis","dial_code":"+1 869","code":"KN","latitude":17.3333,"longitude":-62.75},"SAINT LUCIA":{"name":"Saint Lucia","dial_code":"+1 758","code":"LC","latitude":13.8833,"longitude":-61.1333},"SAINT PIERRE AND MIQUELON":{"name":"Saint Pierre and Miquelon","dial_code":"+508","code":"PM","latitude":46.8333,"longitude":-56.3333},"SAINT VINCENT AND THE GRENADINES":{"name":"Saint Vincent and the Grenadines","dial_code":"+1 784","code":"VC","latitude":13.25,"longitude":-61.2},"SAMOA":{"name":"Samoa","dial_code":"+685","code":"WS","latitude":-13.5833,"longitude":-172.3333},"SAN MARINO":{"name":"San Marino","dial_code":"+378","code":"SM","latitude":43.7667,"longitude":12.4167},"SAO TOME AND PRINCIPE":{"name":"Sao Tome and Principe","dial_code":"+239","code":"ST","latitude":1,"longitude":7},"SAUDI ARABIA":{"name":"Saudi Arabia","dial_code":"+966","code":"SA","latitude":25,"longitude":45},"SENEGAL":{"name":"Senegal","dial_code":"+221","code":"SN","latitude":14,"longitude":-14},"SERBIA":{"name":"Serbia","dial_code":"+381","code":"RS","latitude":44,"longitude":21},"SEYCHELLES":{"name":"Seychelles","dial_code":"+248","code":"SC","latitude":-4.5833,"longitude":55.6667},"SIERRA LEONE":{"name":"Sierra Leone","dial_code":"+232","code":"SL","latitude":8.5,"longitude":-11.5},"SINGAPORE":{"name":"Singapore","dial_code":"+65","code":"SG","latitude":1.3667,"longitude":103.8},"SLOVAKIA":{"name":"Slovakia","dial_code":"+421","code":"SK","latitude":48.6667,"longitude":19.5},"SLOVENIA":{"name":"Slovenia","dial_code":"+386","code":"SI","latitude":46,"longitude":15},"SOLOMON ISLANDS":{"name":"Solomon Islands","dial_code":"+677","code":"SB","latitude":-8,"longitude":159},"SOMALIA":{"name":"Somalia","dial_code":"+252","code":"SO","latitude":10,"longitude":49},"SOUTH AFRICA":{"name":"South Africa","dial_code":"+27","code":"ZA","latitude":-29,"longitude":24},"SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS":{"name":"South Georgia and the South Sandwich Islands","dial_code":"+500","code":"GS","latitude":-54.5,"longitude":-37},"SPAIN":{"name":"Spain","dial_code":"+34","code":"ES","latitude":40,"longitude":-4},"SRI LANKA":{"name":"Sri Lanka","dial_code":"+94","code":"LK","latitude":7,"longitude":81},"SUDAN":{"name":"Sudan","dial_code":"+249","code":"SD","latitude":15,"longitude":30},"SURINAME":{"name":"Suriname","dial_code":"+597","code":"SR","latitude":4,"longitude":-56},"SVALBARD AND JAN MAYEN":{"name":"Svalbard and Jan Mayen","dial_code":"+47","code":"SJ","latitude":78,"longitude":20},"SWAZILAND":{"name":"Swaziland","dial_code":"+268","code":"SZ","latitude":-26.5,"longitude":31.5},"SWEDEN":{"name":"Sweden","dial_code":"+46","code":"SE","latitude":62,"longitude":15},"SWITZERLAND":{"name":"Switzerland","dial_code":"+41","code":"CH","latitude":47,"longitude":8},"SYRIAN ARAB REPUBLIC":{"name":"Syrian Arab Republic","dial_code":"+963","code":"SY","latitude":35,"longitude":38},"TAIWAN":{"name":"Taiwan","dial_code":"+886","code":"TW","latitude":23.5,"longitude":121},"TAJIKISTAN":{"name":"Tajikistan","dial_code":"+992","code":"TJ","latitude":39,"longitude":71},"TANZANIA, UNITED REPUBLIC OF":{"name":"Tanzania, United Republic of","dial_code":"+255","code":"TZ","latitude":-6,"longitude":35},"THAILAND":{"name":"Thailand","dial_code":"+66","code":"TH","latitude":15,"longitude":100},"TIMOR-LESTE":{"name":"Timor-Leste","dial_code":"+670","code":"TL","latitude":-8.55,"longitude":125.5167},"TOGO":{"name":"Togo","dial_code":"+228","code":"TG","latitude":8,"longitude":1.1667},"TOKELAU":{"name":"Tokelau","dial_code":"+690","code":"TK","latitude":-9,"longitude":-172},"TONGA":{"name":"Tonga","dial_code":"+676","code":"TO","latitude":-20,"longitude":-175},"TRINIDAD AND TOBAGO":{"name":"Trinidad and Tobago","dial_code":"+1 868","code":"TT","latitude":11,"longitude":-61},"TUNISIA":{"name":"Tunisia","dial_code":"+216","code":"TN","latitude":34,"longitude":9},"TURKEY":{"name":"Turkey","dial_code":"+90","code":"TR","latitude":39,"longitude":35},"TURKMENISTAN":{"name":"Turkmenistan","dial_code":"+993","code":"TM","latitude":40,"longitude":60},"TURKS AND CAICOS ISLANDS":{"name":"Turks and Caicos Islands","dial_code":"+1 649","code":"TC","latitude":21.75,"longitude":-71.5833},"TUVALU":{"name":"Tuvalu","dial_code":"+688","code":"TV","latitude":-8,"longitude":178},"UGANDA":{"name":"Uganda","dial_code":"+256","code":"UG","latitude":1,"longitude":32},"UKRAINE":{"name":"Ukraine","dial_code":"+380","code":"UA","latitude":49,"longitude":32},"UNITED ARAB EMIRATES":{"name":"United Arab Emirates","dial_code":"+971","code":"AE","latitude":24,"longitude":54},"UNITED KINGDOM":{"name":"United Kingdom","dial_code":"+44","code":"GB","latitude":54,"longitude":-2},"UNITED STATES":{"name":"United States","dial_code":"+1","code":"US","latitude":38,"longitude":-97},"UNITED STATES MINOR OUTLYING ISLANDS":{"name":"United States Minor Outlying Islands","dial_code":"+1581","code":"UM","latitude":19.2833,"longitude":166.6},"URUGUAY":{"name":"Uruguay","dial_code":"+598","code":"UY","latitude":-33,"longitude":-56},"UZBEKISTAN":{"name":"Uzbekistan","dial_code":"+998","code":"UZ","latitude":41,"longitude":64},"VANUATU":{"name":"Vanuatu","dial_code":"+678","code":"VU","latitude":-16,"longitude":167},"VENEZUELA, BOLIVARIAN REPUBLIC OF":{"name":"Venezuela, Bolivarian Republic of","dial_code":"+58","code":"VE","latitude":8,"longitude":-66},"VIET NAM":{"name":"Viet Nam","dial_code":"+84","code":"VN","latitude":16,"longitude":106},"VIRGIN ISLANDS, BRITISH":{"name":"Virgin Islands, British","dial_code":"+1 284","code":"VG","latitude":18.5,"longitude":-64.5},"VIRGIN ISLANDS, U.S.":{"name":"Virgin Islands, U.S.","dial_code":"+1 340","code":"VI","latitude":18.3333,"longitude":-64.8333},"WALLIS AND FUTUNA":{"name":"Wallis and Futuna","dial_code":"+681","code":"WF","latitude":-13.3,"longitude":-176.2},"WESTERN SAHARA":{"name":"Western Sahara","dial_code":"+732","code":"EH","latitude":24.5,"longitude":-13},"YEMEN":{"name":"Yemen","dial_code":"+967","code":"YE","latitude":15,"longitude":48},"ZAMBIA":{"name":"Zambia","dial_code":"+260","code":"ZM","latitude":-15,"longitude":30},"ZIMBABWE":{"name":"Zimbabwe","dial_code":"+263","code":"ZW","latitude":-20,"longitude":30}}';
	$sCountries= json_decode($Countries, true);

	$Content= '<'.$Section.'>';
	foreach($sList as $K=> $V)
	{
		foreach($sCountries as $KK=> $VV)
		{
			if($VV['code']== strtoupper($V['CountryVV']))
			{
				$V['CountryVV']= $VV['dial_code'].' '.$V['CountryVV'];
				break;
			}
		}

		$Content.= '<row no="'.$K.'">
		<FL val="Company">'.self::company().'</FL>
		<FL val="Last Name">'.$V['Full_Name'].'</FL>
		<FL val="Full Name">'.$V['Full_Name'].'</FL>
		<FL val="Email">'.$V['Email'].'</FL>
		<FL val="Phone">'.$V['Phone'].'</FL>
		<FL val="Tag">new</FL>
		<FL val="Brokers name">'.trim($V['Brokers_name']).'</FL>
		<FL val="Amount currency">'.trim($V['Amount_currency']).'</FL>
		<FL val="Payment method">'.trim($V['Payment_method']).'</FL>
		<FL val="Description">'.trim($V['Description']).'</FL>
		<FL val="CountryVV">'.trim($V['CountryVV']).'</FL>
		<FL val="Affiliate ID">'.$V['Affiliate_ID'].'</FL>
		</row>'."\n";
	}

	$Content.= '</'.$Section.'>';
return($Content);
}

zoho_insert($url, $xmlData)
{
	global $user_ID;
	$Authtoken= trim(get_user_meta($user_ID, "zohoauthtoken", 1));

	$param= "authtoken=".$Authtoken."&scope=crmapi&newFormat=1&xmlData=".$xmlData;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
	$response= curl_exec($ch);
	curl_close($ch);

return($response);
}

zoho_txt_parse($Str, $Delimiter= "\t")
{
	$sData= [];
	$aLines= explode("\n", $Str);
	foreach($aLines as $K=> $V)
	{
		$aRow= explode($Delimiter, $V);
		if($aRow[0])
		{
			$hRow['Full_Name']=    $aRow[0];
			if($aRow[1]) $hRow['Full_Name'].= ' '.$aRow[1];
			$hRow['Email']=        $aRow[2];
			$hRow['Phone']=        $aRow[3];
			$hRow['Tag']=          'new';
			$hRow['Country_text']= $aRow[4];
			$hRow['Affiliate ID']= $_REQUEST['Affiliate_ID'];

			$sData[]= $hRow;
		}
	}
return($sData);
}

