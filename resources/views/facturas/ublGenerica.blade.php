<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2" 
xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" 
xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" 
xmlns:ds="http://www.w3.org/2000/09/xmldsig#" 
xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" 
xmlns:sts="urn:dian:gov:co:facturaelectronica:Structures-2-1" 
xmlns:xades="http://uri.etsi.org/01903/v1.3.2#" 
xmlns:xades141="http://uri.etsi.org/01903/v1.4.1#" 
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
xsi:schemaLocation="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2     http://docs.oasis-open.org/ubl/os-UBL-2.1/xsd/maindoc/UBL-Invoice-2.1.xsd">
    <ext:UBLExtensions>
        <ext:UBLExtension>
          <ext:ExtensionContent>
             <sts:DianExtensions>
                <sts:InvoiceControl>
                    <sts:InvoiceAuthorization>{{$resolucion->autorizacion}}</sts:InvoiceAuthorization>
                    <sts:AuthorizationPeriod>
                         <cbc:StartDate>{{$resolucion->fechain}}</cbc:StartDate>
                         <cbc:EndDate>{{$resolucion->fechafi}}</cbc:EndDate>
                     </sts:AuthorizationPeriod>
                     <sts:AuthorizedInvoices>
                         <sts:Prefix>{{$factura->prefijo}}</sts:Prefix>
                         <sts:From>{{$resolucion->inicio}}</sts:From>
                         <sts:To>{{$resolucion->fin}}</sts:To>
                    </sts:AuthorizedInvoices>
                </sts:InvoiceControl>
                <sts:InvoiceSource>
                   <cbc:IdentificationCode listAgencyID="6" listAgencyName="United Nations Economic Commission for Europe" listSchemeURI="urn:oasis:names:specification:ubl:codelist:gc:CountryIdentificationCode-2.1">CO</cbc:IdentificationCode>
                </sts:InvoiceSource>
                <sts:SoftwareProvider>
                   <sts:ProviderID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" schemeID="6" schemeName="31">901318591</sts:ProviderID>
                   <sts:SoftwareID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)">7298e08a-57de-4c43-83a3-573a09928809</sts:SoftwareID>
                </sts:SoftwareProvider>
                <sts:SoftwareSecurityCode schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)">{{$segCode}}</sts:SoftwareSecurityCode>
                <sts:AuthorizationProvider>
                   <sts:AuthorizationProviderID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" schemeID="4" schemeName="31">800197268</sts:AuthorizationProviderID>
                </sts:AuthorizationProvider>
                <sts:QRCode>{{$qrcode}}
                </sts:QRCode>
             </sts:DianExtensions>
          </ext:ExtensionContent>
        </ext:UBLExtension>
        <ext:UBLExtension>
            <ext:ExtensionContent>

            </ext:ExtensionContent>
        </ext:UBLExtension>
    </ext:UBLExtensions>
    <cbc:UBLVersionID>UBL 2.1</cbc:UBLVersionID>
    <cbc:CustomizationID>05</cbc:CustomizationID>
    <cbc:ProfileID>DIAN 2.1: Factura Electrónica de Venta</cbc:ProfileID>
    <cbc:ProfileExecutionID>1</cbc:ProfileExecutionID>
    <cbc:ID>{{$factura->prefijo.$factura->numero}}</cbc:ID>
    <cbc:UUID schemeID="1" schemeName="CUFE-SHA384">{{$cufe}}</cbc:UUID>
    <cbc:IssueDate>{{$hoy->format("Y-m-d")}}</cbc:IssueDate>
    <cbc:IssueTime>{{$hoy->format("H:i:s")}}-05:00</cbc:IssueTime>
    <cbc:DueDate>{{$vencimiento->format("Y-m-d")}}</cbc:DueDate>
    <cbc:InvoiceTypeCode>01</cbc:InvoiceTypeCode>
    <cbc:Note>{{$factura->descripcion}}</cbc:Note>
    <cbc:DocumentCurrencyCode listAgencyID="6" listAgencyName="United Nations Economic Commission for Europe" listID="ISO 4217 Alpha">COP</cbc:DocumentCurrencyCode>
    <cbc:LineCountNumeric>{{count($productos)}}</cbc:LineCountNumeric>
    <cac:InvoicePeriod>
       <cbc:StartDate>{{$hoy->format("Y-m").'-01'}}</cbc:StartDate>
       <cbc:EndDate>{{$finMes->format("Y-m-d")}}</cbc:EndDate>
    </cac:InvoicePeriod>
    <cac:AccountingSupplierParty>
        <cbc:AdditionalAccountID>1</cbc:AdditionalAccountID>
        <cac:Party>
            <cac:PartyName>
                <cbc:Name>CAHORS S.A.S.</cbc:Name>
            </cac:PartyName>
            <cac:PhysicalLocation>
                <cac:Address>
                <cbc:ID>68001</cbc:ID>
                    <cbc:CityName>Bucaramanga</cbc:CityName>
                    <cbc:CountrySubentity>Santander</cbc:CountrySubentity>
                    <cbc:CountrySubentityCode>68</cbc:CountrySubentityCode>
                    <cac:AddressLine>
                        <cbc:Line>Carrera 33 # 49-35 Oficina 300 Centro Comercial Cabecera II Etapa </cbc:Line>
                    </cac:AddressLine>
                    <cac:Country>
                        <cbc:IdentificationCode>CO</cbc:IdentificationCode>
                        <cbc:Name languageID="es">Colombia</cbc:Name>
                    </cac:Country>
                </cac:Address>
            </cac:PhysicalLocation>
            <cac:PartyTaxScheme>
                <cbc:RegistrationName>CAHORS S.A.S.</cbc:RegistrationName>
                <cbc:CompanyID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" schemeID="6" schemeName="31">901318591</cbc:CompanyID>
                <cbc:TaxLevelCode listName="48">O-23</cbc:TaxLevelCode>
                <cac:RegistrationAddress>
                    <cbc:ID>68001</cbc:ID>
                    <cbc:CityName>Bucaramanga</cbc:CityName>
                    <cbc:CountrySubentity>Santander</cbc:CountrySubentity>
                    <cbc:CountrySubentityCode>68</cbc:CountrySubentityCode>
                    <cac:AddressLine>
                        <cbc:Line>Carrera 33 # 49-35 Oficina 300 Centro Comercial Cabecera II Etapa </cbc:Line>
                    </cac:AddressLine>
                    <cac:Country>
                        <cbc:IdentificationCode>CO</cbc:IdentificationCode>
                        <cbc:Name languageID="es">COLOMBIA</cbc:Name>
                    </cac:Country>
                </cac:RegistrationAddress>
                <cac:TaxScheme>
                    <cbc:ID>01</cbc:ID>
                    <cbc:Name>IVA</cbc:Name>
                </cac:TaxScheme>
            </cac:PartyTaxScheme>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName>CAHORS S.A.S.</cbc:RegistrationName>
                <cbc:CompanyID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" schemeID="6" schemeName="31">901318591</cbc:CompanyID>
                <cac:CorporateRegistrationScheme>
                    <cbc:ID>{{$factura->prefijo}}</cbc:ID>
                </cac:CorporateRegistrationScheme>
            </cac:PartyLegalEntity>
            <cac:Contact>
                <cbc:Name>REGIMEN COMUN</cbc:Name>
                <cbc:Telephone>6339215</cbc:Telephone>
                <cbc:ElectronicMail>gestion@cahors.co</cbc:ElectronicMail>
            </cac:Contact>
        </cac:Party>
    </cac:AccountingSupplierParty>
    <cac:AccountingCustomerParty>
       <cbc:AdditionalAccountID>1</cbc:AdditionalAccountID>
       <cac:Party>
           @php
                if($tercero->usuario != null){
                    $tipodoc = 13;
                }else{
                    $tipodoc = 31;
                    $dv = $tercero->empresa->dv;
                }
           @endphp
            <cac:PartyIdentification>
                <cbc:ID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" @isset($dv) schemeID="{{$dv}}" @endisset schemeName="{{$tipodoc}}">{{$tercero->documento}}</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyName>
                <cbc:Name>{{$tercero->nombre}}</cbc:Name>
            </cac:PartyName>
            <cac:PhysicalLocation>
                <cac:Address>
                    <cbc:ID>68001</cbc:ID>
                    <cbc:CityName>{{$tercero->municipio}}</cbc:CityName>
                    <cbc:CountrySubentity>Santander</cbc:CountrySubentity>
                    <cbc:CountrySubentityCode>68</cbc:CountrySubentityCode>
                    <cac:AddressLine>
                        <cbc:Line>{{$tercero->direccion}}</cbc:Line>
                    </cac:AddressLine>
                    <cac:Country>
                        <cbc:IdentificationCode>CO</cbc:IdentificationCode>
                        <cbc:Name languageID="es">Colombia</cbc:Name>
                    </cac:Country>
                </cac:Address>
            </cac:PhysicalLocation>
          <cac:PartyTaxScheme>
             <cbc:RegistrationName>{{$tercero->nombre}}</cbc:RegistrationName>
             <cbc:CompanyID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" @isset($dv) schemeID="{{$dv}}" @endisset schemeName="{{$tipodoc}}">{{$tercero->documento}}</cbc:CompanyID>
             <cbc:TaxLevelCode listName="48">O-47</cbc:TaxLevelCode>
             <cac:TaxScheme>
                <cbc:ID>01</cbc:ID>
                <cbc:Name>IVA</cbc:Name>
             </cac:TaxScheme>
          </cac:PartyTaxScheme>
          <cac:PartyLegalEntity>
            <cbc:RegistrationName>{{$tercero->nombre}}</cbc:RegistrationName>
            <cbc:CompanyID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" @isset($dv) schemeID="{{$dv}}" @endisset schemeName="{{$tipodoc}}">{{$tercero->documento}}</cbc:CompanyID>
          </cac:PartyLegalEntity>
       </cac:Party>
    </cac:AccountingCustomerParty>
    <cac:PaymentMeans>
       <cbc:ID>2</cbc:ID>
       <cbc:PaymentMeansCode>10</cbc:PaymentMeansCode>
       <cbc:PaymentDueDate>{{$vencimiento->format("Y-m-d")}}</cbc:PaymentDueDate>
    </cac:PaymentMeans>
    <cac:TaxTotal>
        <cbc:TaxAmount currencyID="COP">{{number_format($iva, 2, ".", "")}}</cbc:TaxAmount>
        @if ($iva > 0)  
            <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="COP">{{number_format($baseiva, 2, ".", "")}}</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="COP">{{number_format($iva, 2, ".", "")}}</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cbc:Percent>19.00</cbc:Percent>
                    <cac:TaxScheme>
                        <cbc:ID>01</cbc:ID>
                        <cbc:Name>IVA</cbc:Name>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
        @endif
        @if ($siniva > 0)
            <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="COP">{{number_format($siniva, 2, ".", "")}}</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="COP">0.00</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cbc:Percent>0.00</cbc:Percent>
                    <cac:TaxScheme>
                        <cbc:ID>01</cbc:ID>
                        <cbc:Name>IVA</cbc:Name>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
        @endif 
    </cac:TaxTotal>
    <cac:LegalMonetaryTotal>
       <cbc:LineExtensionAmount currencyID="COP">{{number_format($factura->valor-$iva, 2, ".", "")}}</cbc:LineExtensionAmount>
       <cbc:TaxExclusiveAmount currencyID="COP">{{number_format($factura->valor-$iva, 2, ".", "")}}</cbc:TaxExclusiveAmount>
       <cbc:TaxInclusiveAmount currencyID="COP">{{number_format($factura->valor, 2, ".", "")}}</cbc:TaxInclusiveAmount>
       <cbc:PayableAmount currencyID="COP">{{number_format($factura->valor, 2, ".", "")}}</cbc:PayableAmount>
    </cac:LegalMonetaryTotal>
    @for ($i = 0; $i <= count($productos)-1; $i++)
    <cac:InvoiceLine>
        <cbc:ID>{{$i+1}}</cbc:ID>
        <cbc:InvoicedQuantity unitCode="WSD">{{$productos[$i]->cantidad}}</cbc:InvoicedQuantity>
        <cbc:LineExtensionAmount currencyID="COP">{{number_format($productos[$i]->valor, 2, ".", "")}}</cbc:LineExtensionAmount>
        <cbc:FreeOfChargeIndicator>false</cbc:FreeOfChargeIndicator>
        <cac:TaxTotal>
            @if (isset($productos[$i]->iva))
            <cbc:TaxAmount currencyID="COP">{{number_format($productos[$i]->iva, 2, ".", "")}}</cbc:TaxAmount>
            <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="COP">{{number_format($productos[$i]->valor, 2, ".", "")}}</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="COP">{{number_format($productos[$i]->iva, 2, ".", "")}}</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cbc:Percent>19.00</cbc:Percent>
                    <cac:TaxScheme>
                        <cbc:ID>01</cbc:ID>
                        <cbc:Name>IVA</cbc:Name>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
            @else
            <cbc:TaxAmount currencyID="COP">0.00</cbc:TaxAmount>
            <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="COP">{{number_format($productos[$i]->valor, 2, ".", "")}}</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="COP">0.00</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cbc:Percent>0.00</cbc:Percent>
                    <cac:TaxScheme>
                        <cbc:ID>01</cbc:ID>
                        <cbc:Name>IVA</cbc:Name>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
            @endif    
        </cac:TaxTotal>
        <cac:Item>
            <cbc:Description>{{$productos[$i]->nombre}}</cbc:Description>
            <cac:StandardItemIdentification>
                <cbc:ID>{{$productos[$i]->nombre}}</cbc:ID>
            </cac:StandardItemIdentification>
        </cac:Item>
        <cac:Price>
            <cbc:PriceAmount currencyID="COP">{{number_format($productos[$i]->valor/$productos[$i]->cantidad, 2, ".", "")}}</cbc:PriceAmount>
            <cbc:BaseQuantity unitCode="WSD">{{$productos[$i]->cantidad}}</cbc:BaseQuantity>
        </cac:Price>
    </cac:InvoiceLine>
    @endfor
</Invoice>