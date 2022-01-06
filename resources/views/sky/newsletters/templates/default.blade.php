<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!--[if IE]><html xmlns="http://www.w3.org/1999/xhtml" class="ie"><![endif]--><!--[if !IE]><!-->
<html xmlns="http://www.w3.org/1999/xhtml">
<!--<![endif]-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <!--[if !mso]><!--><meta http-equiv="X-UA-Compatible" content="IE=edge" /><!--<![endif]-->
    <meta name="viewport" content="width=device-width" />
    <title>{{ $newsletter->subject }}</title>
    <style type="text/css">
        /* Force Outlook to provide a "view in browser" message */
        #outlook a {
            padding: 0;
        }

        /* Force Hotmail to display emails at full width */
        .ReadMsgBody {
            width: 100%;
        }

        .ExternalClass {
            width: 100%;
        }

        /* Force Hotmail to display normal line spacing */
        .ExternalClass,
        .ExternalClass p,
        .ExternalClass span,
        .ExternalClass font,
        .ExternalClass td,
        .ExternalClass div {
            line-height: 100%;
        }

        /* Prevent WebKit and Windows mobile changing default text sizes */
        body,
        table,
        td,
        p,
        a,
        li,
        blockquote {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        /* Remove spacing between tables in Outlook 2007 and up */
        table,
        td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        /* Allow smoother rendering of resized image in Internet Explorer */
        img {
            -ms-interpolation-mode: bicubic;
        }

        * {
            line-height: inherit;
        }

        [x-apple-data-detectors],
        [href^="tel"],
        [href^="sms"] {
            color: inherit !important;
            text-decoration: none !important;
        }

        body {
            margin: 0;
            padding: 0;
        }

        img {
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
        }

        table {
            border-collapse: collapse !important;
            table-layout: fixed;
        }

        body,
        #bodyTable,
        #bodyCell {
            height: 100% !important;
            margin: 0;
            padding: 0;
            width: 100% !important;
        }

        #bodyCell {
            padding: 20px;
        }

        #templateContainer {
            width: 600px;
        }

        body,
        #bodyTable {
            background-color: #dee0e2;
        }

        #templateContainer{
            border: 1px solid #bbbbbb;
        }

        .text-left {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        h1 {
            color: #364f9d !important;
            display: block;
            font-family: Helvetica;
            font-size: 26px;
            font-style: normal;
            font-weight: bold;
            line-height: 100%;
            letter-spacing: normal;
            margin-top: 0;
            margin-right: 0;
            margin-bottom: 20px;
            margin-left: 0;
            text-align: left;
        }

        h2 {
            color: #4664c0 !important;
            display: block;
            font-family: Helvetica;
            font-size: 20px;
            font-style: normal;
            font-weight: bold;
            line-height: 100%;
            letter-spacing: normal;
            margin-top: 0;
            margin-right: 0;
            margin-bottom: 20px;
            margin-left: 0;
            text-align: left;
        }

        h3 {
            color: #6c84cd !important;
            display: block;
            font-family: Helvetica;
            font-size: 16px;
            font-style: italic;
            font-weight: normal;
            line-height: 100%;
            letter-spacing: normal;
            margin-top: 0;
            margin-right: 0;
            margin-bottom: 20px;
            margin-left: 0;
            text-align: left;
        }

        h4 {
            color: #92a4da !important;
            display: block;
            font-family: Helvetica;
            font-size: 14px;
            font-style: italic;
            font-weight: normal;
            line-height: 100%;
            letter-spacing: normal;
            margin-top: 0;
            margin-right: 0;
            margin-bottom: 20px;
            margin-left: 0;
            text-align: left;
        }

        #templatePreheader {
            background-color: #f4f4f4;
            border-bottom: 1px solid #cccccc;
        }

        .preheaderContent {
            color: #808080;
            font-family: Helvetica;
            font-size: 10px;
            line-height: 125%;
            text-align: left;
        }

        .preheaderContent a:link,
        .preheaderContent a:visited, /* Yahoo! Mail Override */
        .preheaderContent a .yshortcuts /* Yahoo! Mail Override */ {
            color: #606060;
            font-weight: normal;
            text-decoration: underline;
        }

        #templateHeader {
            background-color: #f4f4f4;
            border-top: 1px solid #ffffff;
            border-bottom: 1px solid #cccccc;
        }

        .headerContent {
            color: #505050;
            font-family: Helvetica;
            font-size: 20px;
            font-weight: bold;
            line-height: 100%;
            padding-top: 0;
            padding-right: 0;
            padding-bottom: 0;
            padding-left: 0;
            text-align: left;
            vertical-align: middle;
        }

        .headerContent a:link,
        .headerContent a:visited, /* Yahoo! Mail Override */
        .headerContent a .yshortcuts /* Yahoo! Mail Override */ {
            color: #0090d1;
            font-weight: normal;
            text-decoration: underline;
        }

        .responsiveImage {
            height: auto;
            max-width: 600px;
        }

        .templateColumnContainer{
            width: 260px;
        }

        #templateColumns {
            background-color: #f4f4f4;
        }

        .leftColumnContent {
            color: #505050;
            font-family: Helvetica;
            font-size: 14px;
            line-height: 150%;
            padding-top: 0;
            padding-right: 20px;
            padding-bottom: 20px;
            padding-left: 20px;
            text-align: left;
        }

        .leftColumnContent a:link,
        .leftColumnContent a:visited, /* Yahoo! Mail Override */
        .leftColumnContent a .yshortcuts /* Yahoo! Mail Override */ {
            color: #eb4102;
            font-weight: normal;
            text-decoration: underline;
        }

        .rightColumnContent {
            color: #505050;
            font-family: Helvetica;
            font-size: 14px;
            line-height: 150%;
            padding-top: 0;
            padding-right: 20px;
            padding-bottom: 20px;
            padding-left: 20px;
            text-align: left;
        }

        .rightColumnContent a:link,
        .rightColumnContent a:visited, /* Yahoo! Mail Override */
        .rightColumnContent a .yshortcuts /* Yahoo! Mail Override */ {
            color: #eb4102;
            font-weight: normal;
            text-decoration: underline;
        }

        .leftColumnContent img,
        .rightColumnContent img {
            display: inline;
            height: auto;
            max-width: 260px;
        }

        #templateBody {
            background-color: #f4f4f4;
            border-top: 1px solid #ffffff;
            border-bottom: 1px solid #cccccc;
        }

        .bodyContent{
            color: #505050;
            font-family: Helvetica;
            font-size: 14px;
            line-height: 150%;
            padding-top: 20px;
            padding-right: 20px;
            padding-bottom: 20px;
            padding-left: 20px;
            text-align: justify;
        }

        .bodyContent a:link,
        .bodyContent a:visited, /* Yahoo! Mail Override */
        .bodyContent a .yshortcuts /* Yahoo! Mail Override */ {
            color: #0090d1;
            font-weight: normal;
            text-decoration: underline;
        }

        .bodyContent img {
            display: inline;
            height: auto;
            max-width: 560px;
        }

        #templateFooter {
            background-color: #f4f4f4;
            border-top: 1px solid #ffffff;
        }

        .footerContent {
            color: #808080;
            font-family: Helvetica;
            font-size: 10px;
            line-height: 150%;
            padding-top: 20px;
            padding-right: 20px;
            padding-bottom: 20px;
            padding-left: 20px;
            text-align: left;
        }

        .footerContent a:link,
        .footerContent a:visited, /* Yahoo! Mail Override */
        .footerContent a .yshortcuts, /* Yahoo! Mail Override */
        .footerContent a span {
            color: #606060;
            font-weight: normal;
            text-decoration: underline;
        }

        .disclaimer{
            color: #808080;
            font-family: Helvetica;
            font-size: 10px;
            line-height: 150%;
            padding-top: 20px;
            padding-right: 20px;
            padding-bottom: 20px;
            padding-left: 20px;
            text-align: justify;
        }

        .disclaimer a:link,
        .disclaimer a:visited, /* Yahoo! Mail Override */
        .disclaimer a .yshortcuts, /* Yahoo! Mail Override */
        .disclaimer a span {
            color: #606060;
            font-weight: normal;
            text-decoration: underline;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .table-inline {
            border: 1px solid #ddd;
            width: 100% !important;
            max-width: 100%;
            margin-bottom: 1.375em;
            border-spacing: 0;
            color: #505050;
            font-family: Helvetica;
            font-size: 16px;
            line-height: 100%;

            thead th {
                border-bottom-width: 2px;
            }

            th,
            td {
                padding: 0.5em; /* 8/16 */
            }
        }

        @media only screen and (max-width: 480px) {
            /* Prevent Webkit platforms from changing default text sizes */
            body,
            table,
            td,
            p,
            a,
            li,
            blockquote {
                -webkit-text-size-adjust: none !important;
            }

            /* Prevent iOS Mail from adding padding to the body */
            body {
                width: 100% !important;
                min-width: 100% !important;
            }

            #bodyCell {
                padding: 10px !important;
            }

            #templateContainer {
                max-width: 600px !important;
                width: 100% !important;
            }

            h1 {
                font-size: 24px !important;
                line-height: 100% !important;
            }

            h2 {
                font-size: 20px !important;
                line-height: 100% !important;
            }

            h3 {
                font-size: 18px !important;
                line-height: 100% !important;
            }

            h4 {
                font-size: 16px !important;
                line-height: 100% !important;
            }

            /* Hide the template preheader to save space */
            #templatePreheader {
                display: none !important;
            }

            .responsiveImage {
                height: auto !important;
                max-width: 600px !important;
                width: 100% !important;
            }

            .headerContent {
                font-size: 20px !important;
                line-height: 125% !important;
            }

            .templateColumnContainer {
                display: block !important;
                width: 100% !important;
            }

            .columnImage {
                height: auto !important;
                max-width: 260px !important;
                width: 100% !important;
            }

            .leftColumnContent {
                font-size: 16px !important;
                line-height: 125% !important;
            }

            .rightColumnContent {
                font-size: 16px !important;
                line-height: 125% !important;
            }

            .bodyContent {
                font-size: 18px !important;
                line-height: 125% !important;
                text-align: left !important;
            }

            .table-inline {
                font-size: 18px !important;
                line-height: 125% !important;
            }

            .footerContent {
                font-size: 14px !important;
                line-height: 115% !important;
            }

            /* Place footer social and utility links on their own lines, for easier access */
            .footerContent a {
                display: block !important;
            }

            .disclaimer {
                font-size: 14px !important;
                line-height: 115% !important;
            }
        }
    </style>
  </head>
  <!--[if mso]>
  <body class="mso">
  <![endif]-->
  <!--[if !mso]><!-->
  <body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
  <!--<![endif]-->
    <center>
      <table align="center" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="bodyTable">
        <tr>
          <td align="center" valign="top">
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
              <tr>
                <td align="center" valign="top" style="padding-top:10px;">
                  <img src="cid:newsletter-logo.png" class="responsiveImage" style="height:auto !important;max-width:149px !important;" alt="Sunset Resort Logo" />
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td align="center" valign="top" id="bodyCell">
            <table border="0" cellpadding="0" cellspacing="0" id="templateContainer">
              <tr>
                <td align="center" valign="top">
                  <table border="0" cellpadding="0" cellspacing="0" width="100%" id="templatePreheader">
                    <tr>
                      <td valign="top" class="preheaderContent" style="padding-top:10px; padding-right:20px; padding-bottom:10px; padding-left:20px;">
                          {{ $newsletter->teaser }}
                      </td>
                      <td valign="top" width="180" class="preheaderContent" style="padding-top:10px; padding-right:20px; padding-bottom:10px; padding-left:0;">{!! $onlineView !!}</td>
                    </tr>
                  </table>
                </td>
              </tr>
              {{-- <tr>
                <td align="center" valign="top">
                  <table border="0" cellpadding="0" cellspacing="0" width="100%" id="templateHeader">
                    <tr>
                      <td valign="top" class="headerContent">
                        {{ $newsletter->subject }}
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>--}}
              <tr>
                <td align="center" valign="top">
                  <table border="0" cellpadding="0" cellspacing="0" width="100%" id="templateBody">
                    <tr>
                      <td valign="top" class="bodyContent">
                          <h1>{{ $newsletter->subject }}</h1>
                          {!! $body !!}
                          {!! $signature !!}
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td align="center" valign="top">
                  <table border="0" cellpadding="0" cellspacing="0" width="100%" id="templateFooter">
                    <tr>
                      <td valign="top" class="footerContent">{!! $links !!}</td>
                    </tr>
                    <tr>
                      <td valign="top" class="footerContent" style="padding-top:0;"><em>{{ $copyright }}</em></td>
                    </tr>
                    <tr>
                      <td valign="top" class="disclaimer" style="padding-top:0; padding-bottom:40px;">{!! $disclaimer !!}</td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </center>
  </body>
</html>
