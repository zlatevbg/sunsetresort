<?php

return [

    'years' => '[1,1] год|[2,Inf] лет',

    // All Years, All Options
    '1-1' => '',
    '1-3' => '',
    '4-4' => 'Арендатор обязан оплачивать все ремонты, относящийся к повреждениям, которые связаны с пользованием Апартамента по договору, а также другие повреждения, причиненные виновно им или субарендаторами.',
    '4-5' => 'При расторжении Договора Арендатор обязан вернуть Квартиру Арендодателю в том состоянии, в котором она была арендована, учитывая нормальный износ и обветшание вследствие оговоренного использования. Под нормальным износом и обветшанием имеется в виду оборудование, мебель, декорации и технические установки в квартире, которые вследствие их многолетнего использования и амортизации портятся, ломаются или получают дефекты и требуют ремонта или замены. В таких случаях все расходы на устранение дефектов, ремонт или замену оборудования, мебели, декораций и технических установок внутри квартиры оплачиваются Арендодателем, и ему следует их устранить их как можно скорее.',
    '5-4-5-6' => '',
    // Custom furniture, All Years, All Options
    'custom-1-1' => '\sсключва настоящия договор на свой риск, като',
    'custom-1-3' => '\sна риск на наемодателя',
    'custom-4-4' => 'Наемателят не е длъжен да заплаща на наемодателя каквито и да е било вреди или щети, причинени от ползването на имота, на мебелите и/или на електрическите и други уреди и вещи в него, причинени от него, негови служители или пренаематели, по време на действие на настоящия договор.',
    'custom-4-5' => 'При прекратяване на Договора Наемателят е длъжен да върне Апартамента на Наемодателя.',
    'custom-5-4-5-6' => '\n5.4. Наемодателят сключва настоящия договор изцяло на свой риск.\n5.5. Наемодателят дава изричното си съгласие, че Наемателят и/или негови служители, и/или негови пренаематели не носят каквато и да е била отговорност, съответно не дължат, каквото и да е било обезщетение за причинените, по време на действие на настоящия договор, вреди от ползването на имота, на мебелите и/или на електрическите и други уреди и вещи в него.\n5.6. В случай на повреда върху имота, на мебелите и/или на електрическите и други уреди и вещи в него, наемодателят е длъжен да предостави средства на наемателя за възстановяване на съответната вещ или за извършване на съответния ремонт, с цел позването на имота. В случай, че наемодателят не предостави необходимите средства за съответния ремонт или подмяна на вещ, то наемателят има право по свое осмотрение да извърши за сметка на наемодателя съответния ремонт и/или подмяна.',

    // All Years, All Options, MM Fees For Current Year
    '6-3' => '\n6.3. В случае прекращения настоящего договора Арендодателем, он должен заплатить штраф в размере суммы которую Арендатор заплатил Управляющему этажной собственности за текущий календарный год.',

    // 1 Year: Option 1
    // 3 Years: All Options
    '1-3-1' => '3.1. Настоящий Договор заключается сроком на :period, то есть с :from по :to года.',
    // 1 Year: Option 2 - Option 6
    '2-3-1' => '3.1. Настоящий Договор заключается для периодом с :from (включительно) по :to (включительно) года.',

    // All Years: Option 1
    '1-4-6' => '\n4.6. Арендатор будет позволять Арендодателю использовать Квартиру в периоды с :from1 года до :to1 года:period2 в личных целях, при том условии, что Арендодатель уведомит Арендатора в письменной форме не менее чем за пятнадцать дней до каждого такого использования.',
    '1-4-6-period2' => ' и с :from2 года до :to2 года,',
    '1-4-7' => '\n4.7. Арендатор организует и осуществляет за свой счет в течение двухлетнего периода с даты подписания настоящего договора, замена определенного инвентаря в квартире в зависимости от степени износа и текущего состояния этих предметов с целью удовлетворения требований туроператора. Это относится к следующим пунктам: телевизор; шторы; матрасы; душевая кабина; ширма и уплотнение ванны; ковер в спальне; замена обивка дивана / кресла.',
    // All Years: Option 5 - Option 6
    '2-4-6' => '\n4.6. Арендатор позволит Арендодателю пользоваться Апартаментом в течение :period недель подряд каждый год, которые даты должны быть указаны Арендодателем и подтвержденных в письменной форме Арендатором не позднее чем 1 апреля каждый год. Личного пользования можно разделить на максимум 2-х отдельных заказов в течение срока аренды.',
    '2-4-6-covid' => '\n4.6. Арендатор позволит Арендодателю пользоваться Апартаментом в течение 2 (две) недель подряд, которые даты должны быть указаны Арендодателем и подтвержденных в письменной форме Арендатором не позднее чем 15 дней с даты заезда. Личного пользования может быть только один раз, без перерыва в течение 14 дней.',

    // All Years: Option 1 - Option 3
    '1-2-1' => 'Часть этой суммы, а именно € :mmfee будет переведена на банковский счет Управляющего зданием, в качестве оплаты таксы обслуживания и управления Апартаментом за :year год.\n',
    '1-2-2' => 'Остальная часть арендной платы за :year год., будет оплачена Арендодателю на его банковский счет до :deadline.\n',
    '1-2-3' => 'Арендодатель заявляет, что принимает указанные в предыдущих пунктах платежи, как действительные и регулярные выплаты арендной платы.\n',
    '1-2-4' => 'Арендатор имеет право удержать арендную плату из сумм, причитающихся с Арендодателя за потребление воды, электроэнергии, телефона и любых иных услуг, предоставляемых Арендодателю или его гостям на территории комплекса. Арендатор имеет право вычесть суммы на покрытие убытков, причиненных Арендодателем или его гостями (даже если они не проживают в квартире) на общих участках и на территории здания и комплекса в целом.',

    // 1 Year: Option 4 - Option 6
    '2-2-1' => 'Все стоимость аренды в статье 2.1 , должны быть оплачены Арендатором от имени Арендодателя как перевод на банковский счет Управляющего зданием, в качестве оплаты таксы обслуживания и управления Апартаментом за :year год.\n',
    '2-2-2' => 'Арендодатель заявляет, что принимает указанные в предыдущих пунктах платеж, как действительные и регулярные выплаты арендной платы.',

    // 3 Years: All Options
    '3-2-1' => 'За период с :from по :to будут выплачены € :rent.',

    // 3 Years: Option 4 - Option 6
    '4-2-1' => 'Этой суммы будет переведена на банковский счет Управляющего зданием, в качестве оплаты таксы обслуживания и управления Апартаментом за :year год.\n',

    // 3 Years: Option 2 - Option 6
    '3-2' => '\n3.2. Срок аренды для использования имущества Арендатором за каждый год из период действия контракта является - :from (включительно) до :to (включительно).',

    'proxy-person' => 'ЕГН (единый гражданский номер) :egn, личная идентификационная карточка № :passport, выдана :issuedat года :issuedby, адрес прописки: :address',
    'proxy-company' => 'ЕИК :bulstat, место нахождения и юридический адрес: :address',

    'signatures' => 'ПОДПИСИ',
    'tenant' => 'За <strong>Наемателя</strong> / Для <strong>Арендатору</strong>',
    'landlord' => 'За <strong>Наемодателя</strong> / Для <strong>Арендодателю</strong>',
    'page' => 'Страница <strong>:pagefrom</strong> от <strong>:pageto</strong>',

    'mm-year-1-1' => 'годы срока действия договора',

];