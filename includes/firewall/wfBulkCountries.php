<?php
if (!defined('WTITAN_VERSION')) { exit; }

$wfBulkCountries = array(
"AD" => __("Andorra", 'wordfence'),
"AE" => __("United Arab Emirates", 'wordfence'),
"AF" => __("Afghanistan", 'wordfence'),
"AG" => __("Antigua and Barbuda", 'wordfence'),
"AI" => __("Anguilla", 'wordfence'),
"AL" => __("Albania", 'wordfence'),
"AM" => __("Armenia", 'wordfence'),
"AO" => __("Angola", 'wordfence'),
"AQ" => __("Antarctica", 'wordfence'),
"AR" => __("Argentina", 'wordfence'),
"AS" => __("American Samoa", 'wordfence'),
"AT" => __("Austria", 'wordfence'),
"AU" => __("Australia", 'wordfence'),
"AW" => __("Aruba", 'wordfence'),
"AX" => __("Aland Islands", 'wordfence'),
"AZ" => __("Azerbaijan", 'wordfence'),
"BA" => __("Bosnia and Herzegovina", 'wordfence'),
"BB" => __("Barbados", 'wordfence'),
"BD" => __("Bangladesh", 'wordfence'),
"BE" => __("Belgium", 'wordfence'),
"BF" => __("Burkina Faso", 'wordfence'),
"BG" => __("Bulgaria", 'wordfence'),
"BH" => __("Bahrain", 'wordfence'),
"BI" => __("Burundi", 'wordfence'),
"BJ" => __("Benin", 'wordfence'),
"BL" => __("Saint Bartelemey", 'wordfence'),
"BM" => __("Bermuda", 'wordfence'),
"BN" => __("Brunei Darussalam", 'wordfence'),
"BO" => __("Bolivia", 'wordfence'),
"BQ" => __("Bonaire, Saint Eustatius and Saba", 'wordfence'),
"BR" => __("Brazil", 'wordfence'),
"BS" => __("Bahamas", 'wordfence'),
"BT" => __("Bhutan", 'wordfence'),
"BV" => __("Bouvet Island", 'wordfence'),
"BW" => __("Botswana", 'wordfence'),
"BY" => __("Belarus", 'wordfence'),
"BZ" => __("Belize", 'wordfence'),
"CA" => __("Canada", 'wordfence'),
"CC" => __("Cocos (Keeling) Islands", 'wordfence'),
"CD" => __("Congo, The Democratic Republic of the", 'wordfence'),
"CF" => __("Central African Republic", 'wordfence'),
"CG" => __("Congo", 'wordfence'),
"CH" => __("Switzerland", 'wordfence'),
"CI" => __("Cote dIvoire", 'wordfence'),
"CK" => __("Cook Islands", 'wordfence'),
"CL" => __("Chile", 'wordfence'),
"CM" => __("Cameroon", 'wordfence'),
"CN" => __("China", 'wordfence'),
"CO" => __("Colombia", 'wordfence'),
"CR" => __("Costa Rica", 'wordfence'),
"CU" => __("Cuba", 'wordfence'),
"CV" => __("Cape Verde", 'wordfence'),
"CW" => __("Curacao", 'wordfence'),
"CX" => __("Christmas Island", 'wordfence'),
"CY" => __("Cyprus", 'wordfence'),
"CZ" => __("Czech Republic", 'wordfence'),
"DE" => __("Germany", 'wordfence'),
"DJ" => __("Djibouti", 'wordfence'),
"DK" => __("Denmark", 'wordfence'),
"DM" => __("Dominica", 'wordfence'),
"DO" => __("Dominican Republic", 'wordfence'),
"DZ" => __("Algeria", 'wordfence'),
"EC" => __("Ecuador", 'wordfence'),
"EE" => __("Estonia", 'wordfence'),
"EG" => __("Egypt", 'wordfence'),
"EH" => __("Western Sahara", 'wordfence'),
"ER" => __("Eritrea", 'wordfence'),
"ES" => __("Spain", 'wordfence'),
"ET" => __("Ethiopia", 'wordfence'),
"EU" => __("Europe", 'wordfence'),
"FI" => __("Finland", 'wordfence'),
"FJ" => __("Fiji", 'wordfence'),
"FK" => __("Falkland Islands (Malvinas)", 'wordfence'),
"FM" => __("Micronesia, Federated States of", 'wordfence'),
"FO" => __("Faroe Islands", 'wordfence'),
"FR" => __("France", 'wordfence'),
"GA" => __("Gabon", 'wordfence'),
"GB" => __("United Kingdom", 'wordfence'),
"GD" => __("Grenada", 'wordfence'),
"GE" => __("Georgia", 'wordfence'),
"GF" => __("French Guiana", 'wordfence'),
"GG" => __("Guernsey", 'wordfence'),
"GH" => __("Ghana", 'wordfence'),
"GI" => __("Gibraltar", 'wordfence'),
"GL" => __("Greenland", 'wordfence'),
"GM" => __("Gambia", 'wordfence'),
"GN" => __("Guinea", 'wordfence'),
"GP" => __("Guadeloupe", 'wordfence'),
"GQ" => __("Equatorial Guinea", 'wordfence'),
"GR" => __("Greece", 'wordfence'),
"GS" => __("South Georgia and the South Sandwich Islands", 'wordfence'),
"GT" => __("Guatemala", 'wordfence'),
"GU" => __("Guam", 'wordfence'),
"GW" => __("Guinea-Bissau", 'wordfence'),
"GY" => __("Guyana", 'wordfence'),
"HK" => __("Hong Kong", 'wordfence'),
"HM" => __("Heard Island and McDonald Islands", 'wordfence'),
"HN" => __("Honduras", 'wordfence'),
"HR" => __("Croatia", 'wordfence'),
"HT" => __("Haiti", 'wordfence'),
"HU" => __("Hungary", 'wordfence'),
"ID" => __("Indonesia", 'wordfence'),
"IE" => __("Ireland", 'wordfence'),
"IL" => __("Israel", 'wordfence'),
"IM" => __("Isle of Man", 'wordfence'),
"IN" => __("India", 'wordfence'),
"IO" => __("British Indian Ocean Territory", 'wordfence'),
"IQ" => __("Iraq", 'wordfence'),
"IR" => __("Iran, Islamic Republic of", 'wordfence'),
"IS" => __("Iceland", 'wordfence'),
"IT" => __("Italy", 'wordfence'),
"JE" => __("Jersey", 'wordfence'),
"JM" => __("Jamaica", 'wordfence'),
"JO" => __("Jordan", 'wordfence'),
"JP" => __("Japan", 'wordfence'),
"KE" => __("Kenya", 'wordfence'),
"KG" => __("Kyrgyzstan", 'wordfence'),
"KH" => __("Cambodia", 'wordfence'),
"KI" => __("Kiribati", 'wordfence'),
"KM" => __("Comoros", 'wordfence'),
"KN" => __("Saint Kitts and Nevis", 'wordfence'),
"KP" => __("Korea, Democratic Peoples Republic of", 'wordfence'),
"KR" => __("Korea, Republic of", 'wordfence'),
"KW" => __("Kuwait", 'wordfence'),
"KY" => __("Cayman Islands", 'wordfence'),
"KZ" => __("Kazakhstan", 'wordfence'),
"LA" => __("Lao Peoples Democratic Republic", 'wordfence'),
"LB" => __("Lebanon", 'wordfence'),
"LC" => __("Saint Lucia", 'wordfence'),
"LI" => __("Liechtenstein", 'wordfence'),
"LK" => __("Sri Lanka", 'wordfence'),
"LR" => __("Liberia", 'wordfence'),
"LS" => __("Lesotho", 'wordfence'),
"LT" => __("Lithuania", 'wordfence'),
"LU" => __("Luxembourg", 'wordfence'),
"LV" => __("Latvia", 'wordfence'),
"LY" => __("Libyan Arab Jamahiriya", 'wordfence'),
"MA" => __("Morocco", 'wordfence'),
"MC" => __("Monaco", 'wordfence'),
"MD" => __("Moldova, Republic of", 'wordfence'),
"ME" => __("Montenegro", 'wordfence'),
"MF" => __("Saint Martin", 'wordfence'),
"MG" => __("Madagascar", 'wordfence'),
"MH" => __("Marshall Islands", 'wordfence'),
"MK" => __("Macedonia", 'wordfence'),
"ML" => __("Mali", 'wordfence'),
"MM" => __("Myanmar", 'wordfence'),
"MN" => __("Mongolia", 'wordfence'),
"MO" => __("Macao", 'wordfence'),
"MP" => __("Northern Mariana Islands", 'wordfence'),
"MQ" => __("Martinique", 'wordfence'),
"MR" => __("Mauritania", 'wordfence'),
"MS" => __("Montserrat", 'wordfence'),
"MT" => __("Malta", 'wordfence'),
"MU" => __("Mauritius", 'wordfence'),
"MV" => __("Maldives", 'wordfence'),
"MW" => __("Malawi", 'wordfence'),
"MX" => __("Mexico", 'wordfence'),
"MY" => __("Malaysia", 'wordfence'),
"MZ" => __("Mozambique", 'wordfence'),
"NA" => __("Namibia", 'wordfence'),
"NC" => __("New Caledonia", 'wordfence'),
"NE" => __("Niger", 'wordfence'),
"NF" => __("Norfolk Island", 'wordfence'),
"NG" => __("Nigeria", 'wordfence'),
"NI" => __("Nicaragua", 'wordfence'),
"NL" => __("Netherlands", 'wordfence'),
"NO" => __("Norway", 'wordfence'),
"NP" => __("Nepal", 'wordfence'),
"NR" => __("Nauru", 'wordfence'),
"NU" => __("Niue", 'wordfence'),
"NZ" => __("New Zealand", 'wordfence'),
"OM" => __("Oman", 'wordfence'),
"PA" => __("Panama", 'wordfence'),
"PE" => __("Peru", 'wordfence'),
"PF" => __("French Polynesia", 'wordfence'),
"PG" => __("Papua New Guinea", 'wordfence'),
"PH" => __("Philippines", 'wordfence'),
"PK" => __("Pakistan", 'wordfence'),
"PL" => __("Poland", 'wordfence'),
"PM" => __("Saint Pierre and Miquelon", 'wordfence'),
"PN" => __("Pitcairn", 'wordfence'),
"PR" => __("Puerto Rico", 'wordfence'),
"PS" => __("Palestinian Territory", 'wordfence'),
"PT" => __("Portugal", 'wordfence'),
"PW" => __("Palau", 'wordfence'),
"PY" => __("Paraguay", 'wordfence'),
"QA" => __("Qatar", 'wordfence'),
"RE" => __("Reunion", 'wordfence'),
"RO" => __("Romania", 'wordfence'),
"RS" => __("Serbia", 'wordfence'),
"RU" => __("Russian Federation", 'wordfence'),
"RW" => __("Rwanda", 'wordfence'),
"SA" => __("Saudi Arabia", 'wordfence'),
"SB" => __("Solomon Islands", 'wordfence'),
"SC" => __("Seychelles", 'wordfence'),
"SD" => __("Sudan", 'wordfence'),
"SE" => __("Sweden", 'wordfence'),
"SG" => __("Singapore", 'wordfence'),
"SH" => __("Saint Helena", 'wordfence'),
"SI" => __("Slovenia", 'wordfence'),
"SJ" => __("Svalbard and Jan Mayen", 'wordfence'),
"SK" => __("Slovakia", 'wordfence'),
"SL" => __("Sierra Leone", 'wordfence'),
"SM" => __("San Marino", 'wordfence'),
"SN" => __("Senegal", 'wordfence'),
"SO" => __("Somalia", 'wordfence'),
"SR" => __("Suriname", 'wordfence'),
"ST" => __("Sao Tome and Principe", 'wordfence'),
"SV" => __("El Salvador", 'wordfence'),
"SX" => __("Sint Maarten", 'wordfence'),
"SY" => __("Syrian Arab Republic", 'wordfence'),
"SZ" => __("Swaziland", 'wordfence'),
"TC" => __("Turks and Caicos Islands", 'wordfence'),
"TD" => __("Chad", 'wordfence'),
"TF" => __("French Southern Territories", 'wordfence'),
"TG" => __("Togo", 'wordfence'),
"TH" => __("Thailand", 'wordfence'),
"TJ" => __("Tajikistan", 'wordfence'),
"TK" => __("Tokelau", 'wordfence'),
"TL" => __("Timor-Leste", 'wordfence'),
"TM" => __("Turkmenistan", 'wordfence'),
"TN" => __("Tunisia", 'wordfence'),
"TO" => __("Tonga", 'wordfence'),
"TR" => __("Turkey", 'wordfence'),
"TT" => __("Trinidad and Tobago", 'wordfence'),
"TV" => __("Tuvalu", 'wordfence'),
"TW" => __("Taiwan", 'wordfence'),
"TZ" => __("Tanzania, United Republic of", 'wordfence'),
"UA" => __("Ukraine", 'wordfence'),
"UG" => __("Uganda", 'wordfence'),
"UM" => __("United States Minor Outlying Islands", 'wordfence'),
"US" => __("United States", 'wordfence'),
"UY" => __("Uruguay", 'wordfence'),
"UZ" => __("Uzbekistan", 'wordfence'),
"VA" => __("Holy See (Vatican City State)", 'wordfence'),
"VC" => __("Saint Vincent and the Grenadines", 'wordfence'),
"VE" => __("Venezuela", 'wordfence'),
"VG" => __("Virgin Islands, British", 'wordfence'),
"VI" => __("Virgin Islands, U.S.", 'wordfence'),
"VN" => __("Vietnam", 'wordfence'),
"VU" => __("Vanuatu", 'wordfence'),
"WF" => __("Wallis and Futuna", 'wordfence'),
"WS" => __("Samoa", 'wordfence'),
"XK" => __("Kosovo", 'wordfence'),
"YE" => __("Yemen", 'wordfence'),
"YT" => __("Mayotte", 'wordfence'),
"ZA" => __("South Africa", 'wordfence'),
"ZM" => __("Zambia", 'wordfence'),
"ZW" => __("Zimbabwe", 'wordfence'),
);
