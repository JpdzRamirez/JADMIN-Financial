<CreditNote xmlns="urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2" 
xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" 
xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" 
xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
xmlns:sts="urn:dian:gov:co:facturaelectronica:Structures-2-1"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:xades="http://uri.etsi.org/01903/v1.3.2#" 
xmlns:xades141="http://uri.etsi.org/01903/v1.4.1#" 
xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
    <ext:UBLExtensions>
        <ext:UBLExtension>
          <ext:ExtensionContent>
             <sts:DianExtensions>
                <sts:InvoiceSource>
                   <cbc:IdentificationCode listAgencyID="6" listAgencyName="United Nations Economic Commission for Europe" listSchemeURI="urn:oasis:names:specification:ubl:codelist:gc:CountryIdentificationCode-2.1">CO</cbc:IdentificationCode>
                </sts:InvoiceSource>
                <sts:SoftwareProvider>
                   <sts:ProviderID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" schemeID="6" schemeName="31">NIT</sts:ProviderID>
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
    <cbc:CustomizationID>20</cbc:CustomizationID>
    <cbc:ProfileID>DIAN 2.1: Nota Crédito de Factura Electrónica de Venta</cbc:ProfileID>
    <cbc:ProfileExecutionID>1</cbc:ProfileExecutionID>
    <cbc:ID>{{$notaCredito->prefijo.$notaCredito->numero}}</cbc:ID>
    <cbc:UUID schemeID="1" schemeName="CUDE-SHA384">{{$cude}}</cbc:UUID>
    <cbc:IssueDate>{{$hoy->format("Y-m-d")}}</cbc:IssueDate>
    <cbc:IssueTime>{{$hoy->format("H:i:s")}}-05:00</cbc:IssueTime>
    <cbc:CreditNoteTypeCode>91</cbc:CreditNoteTypeCode>
    <cbc:Note>{{$notaCredito->concepto}}</cbc:Note>
    <cbc:DocumentCurrencyCode>COP</cbc:DocumentCurrencyCode>
    @if ($desprestamo > 0)
        <cbc:LineCountNumeric>5</cbc:LineCountNumeric>
    @else
        <cbc:LineCountNumeric>4</cbc:LineCountNumeric>
    @endif
    <cac:DiscrepancyResponse>
        <cbc:ReferenceID>{{$factura->prefijo}}{{$factura->numero}}</cbc:ReferenceID>
        <cbc:ResponseCode>1</cbc:ResponseCode>
        <cbc:Description>{{$notaCredito->concepto}}</cbc:Description>
    </cac:DiscrepancyResponse>
    <cac:BillingReference>
        <cac:InvoiceDocumentReference>
            <cbc:ID>{{$factura->prefijo}}{{$factura->numero}}</cbc:ID>
            <cbc:UUID schemeName="CUFE-SHA384">{{$factura->cufe}}</cbc:UUID>
            <cbc:IssueDate>{{\Carbon\Carbon::parse($factura->fecha)->format('Y-m-d')}}</cbc:IssueDate>
        </cac:InvoiceDocumentReference>
    </cac:BillingReference>
    <cac:AccountingSupplierParty>
        <cbc:AdditionalAccountID>1</cbc:AdditionalAccountID>
        <cac:Party>
            <cac:PartyName>
                <cbc:Name>JADMIN</cbc:Name>
            </cac:PartyName>
            <cac:PhysicalLocation>
                <cac:Address>
                <cbc:ID>68001</cbc:ID>
                    <cbc:CityName>Bucaramanga</cbc:CityName>
                    <cbc:CountrySubentity>Santander</cbc:CountrySubentity>
                    <cbc:CountrySubentityCode>68</cbc:CountrySubentityCode>
                    <cac:AddressLine>
                        <cbc:Line>DIRECCION </cbc:Line>
                    </cac:AddressLine>
                    <cac:Country>
                        <cbc:IdentificationCode>CO</cbc:IdentificationCode>
                        <cbc:Name languageID="es">Colombia</cbc:Name>
                    </cac:Country>
                </cac:Address>
            </cac:PhysicalLocation>
            <cac:PartyTaxScheme>
                <cbc:RegistrationName>JADMIN</cbc:RegistrationName>
                <cbc:CompanyID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" schemeID="6" schemeName="31">NIT</cbc:CompanyID>
                <cbc:TaxLevelCode listName="48">O-23</cbc:TaxLevelCode>
                <cac:RegistrationAddress>
                    <cbc:ID>68001</cbc:ID>
                    <cbc:CityName>Bucaramanga</cbc:CityName>
                    <cbc:CountrySubentity>Santander</cbc:CountrySubentity>
                    <cbc:CountrySubentityCode>68</cbc:CountrySubentityCode>
                    <cac:AddressLine>
                        <cbc:Line>DIRECCION </cbc:Line>
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
                <cbc:RegistrationName>JADMIN</cbc:RegistrationName>
                <cbc:CompanyID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" schemeID="6" schemeName="31">NIT</cbc:CompanyID>
                <cac:CorporateRegistrationScheme>
                    <cbc:ID>NC</cbc:ID>
                </cac:CorporateRegistrationScheme>
            </cac:PartyLegalEntity>
            <cac:Contact>
                <cbc:Name>REGIMEN COMUN</cbc:Name>
                <cbc:Telephone>6339215</cbc:Telephone>
                <cbc:ElectronicMail>gestion@JADMINc:ElectronicMail>
            </cac:Contact>
        </cac:Party>
    </cac:AccountingSupplierParty>
    <cac:AccountingCustomerParty>
       <cbc:AdditionalAccountID>1</cbc:AdditionalAccountID>
       <cac:Party>
            <cac:PartyIdentification>
                <cbc:ID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" schemeName="13">{{$factura->tercero->documento}}</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyName>
                <cbc:Name>{{$factura->tercero->nombre}}</cbc:Name>
            </cac:PartyName>
            <cac:PhysicalLocation>
                <cac:Address>
                    <cbc:ID>68001</cbc:ID>
                    <cbc:CityName>{{$factura->credito->cliente->municipio}}</cbc:CityName>
                    <cbc:CountrySubentity>Santander</cbc:CountrySubentity>
                    <cbc:CountrySubentityCode>68</cbc:CountrySubentityCode>
                    <cac:AddressLine>
                        <cbc:Line>{{$factura->credito->cliente->direccion}}</cbc:Line>
                    </cac:AddressLine>
                    <cac:Country>
                        <cbc:IdentificationCode>CO</cbc:IdentificationCode>
                        <cbc:Name languageID="es">Colombia</cbc:Name>
                    </cac:Country>
                </cac:Address>
            </cac:PhysicalLocation>
          <cac:PartyTaxScheme>
             <cbc:RegistrationName>{{$factura->tercero->nombre}}</cbc:RegistrationName>
             <cbc:CompanyID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" schemeName="13">{{$factura->tercero->documento}}</cbc:CompanyID>
             <cbc:TaxLevelCode listName="48">O-47</cbc:TaxLevelCode>
             <cac:TaxScheme>
                <cbc:ID>01</cbc:ID>
                <cbc:Name>IVA</cbc:Name>
             </cac:TaxScheme>
          </cac:PartyTaxScheme>
          <cac:PartyLegalEntity>
            <cbc:RegistrationName>{{$factura->tercero->nombre}}</cbc:RegistrationName>
            <cbc:CompanyID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" schemeName="13">{{$factura->tercero->documento}}</cbc:CompanyID>
          </cac:PartyLegalEntity>
          <cac:Contact>
                <cbc:Name>{{$factura->tercero->nombre}}</cbc:Name>
                <cbc:Telephone>{{$factura->credito->cliente->celular}}</cbc:Telephone>
                <cbc:ElectronicMail>{{$factura->credito->cliente->email}}</cbc:ElectronicMail>
            </cac:Contact>
       </cac:Party>
    </cac:AccountingCustomerParty>
    <cac:PaymentMeans>
       <cbc:ID>2</cbc:ID>
       <cbc:PaymentMeansCode>1</cbc:PaymentMeansCode>
       <cbc:PaymentDueDate>{{$vencimiento}}</cbc:PaymentDueDate>
    </cac:PaymentMeans>
    <cac:TaxTotal>
        <cbc:TaxAmount currencyID="COP">{{number_format($iva, 2, ".", "")}}</cbc:TaxAmount>
        <cbc:TaxEvidenceIndicator>false</cbc:TaxEvidenceIndicator>
        <cac:TaxSubtotal>
            <cbc:TaxableAmount currencyID="COP">{{number_format($excluido, 2, ".", "")}}</cbc:TaxableAmount>
            <cbc:TaxAmount currencyID="COP">0.00</cbc:TaxAmount>
            <cac:TaxCategory>
                <cbc:Percent>0.00</cbc:Percent>
                <cac:TaxScheme>
                    <cbc:ID>01</cbc:ID>
                    <cbc:Name>IVA</cbc:Name>
                </cac:TaxScheme>
            </cac:TaxCategory>
        </cac:TaxSubtotal>
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
    </cac:TaxTotal>
    <cac:LegalMonetaryTotal>
       <cbc:LineExtensionAmount currencyID="COP">{{number_format($excluido+$baseiva, 2, ".", "")}}</cbc:LineExtensionAmount>
       <cbc:TaxExclusiveAmount currencyID="COP">{{number_format($excluido+$baseiva, 2, ".", "")}}</cbc:TaxExclusiveAmount>
       <cbc:TaxInclusiveAmount currencyID="COP">{{number_format($excluido+$baseiva+$iva, 2, ".", "")}}</cbc:TaxInclusiveAmount>
       <cbc:PayableAmount currencyID="COP">{{number_format($excluido+$baseiva+$iva, 2, ".", "")}}</cbc:PayableAmount>
    </cac:LegalMonetaryTotal>
    <cac:CreditNoteLine>
        <cbc:ID>1</cbc:ID>
        <cbc:CreditedQuantity unitCode="WSD">1.00</cbc:CreditedQuantity>
        <cbc:LineExtensionAmount currencyID="COP">{{number_format($desinteres, 2, ".", "")}}</cbc:LineExtensionAmount>
        <cac:TaxTotal>
            <cbc:TaxAmount currencyID="COP">0.00</cbc:TaxAmount>
            <cbc:TaxEvidenceIndicator>false</cbc:TaxEvidenceIndicator>
            <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="COP">{{number_format($desinteres, 2, ".", "")}}</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="COP">0.00</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cbc:Percent>0.00</cbc:Percent>
                    <cac:TaxScheme>
                        <cbc:ID>01</cbc:ID>
                        <cbc:Name>IVA</cbc:Name>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
        </cac:TaxTotal>
        <cac:Item>
            <cbc:Description>INTERESES CORRIENTE LIBRE INVERSION</cbc:Description>
            <cac:StandardItemIdentification>
                <cbc:ID>INTERESES CORRIENTE LIBRE INVERSION</cbc:ID>
            </cac:StandardItemIdentification>
        </cac:Item>
        <cac:Price>
            <cbc:PriceAmount currencyID="COP">{{number_format($desinteres, 2, ".", "")}}</cbc:PriceAmount>
            <cbc:BaseQuantity unitCode="WSD">1.00</cbc:BaseQuantity>
        </cac:Price>
    </cac:CreditNoteLine>
    <cac:CreditNoteLine>
        <cbc:ID>2</cbc:ID>
        <cbc:CreditedQuantity unitCode="WSD">1.00</cbc:CreditedQuantity>
        <cbc:LineExtensionAmount currencyID="COP">{{number_format($descentrales, 2, ".", "")}}</cbc:LineExtensionAmount>
        <cac:TaxTotal>
            <cbc:TaxAmount currencyID="COP">{{number_format($descentrales*0.19, 2, ".", "")}}</cbc:TaxAmount>
            <cbc:TaxEvidenceIndicator>false</cbc:TaxEvidenceIndicator>
            <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="COP">{{number_format($descentrales, 2, ".", "")}}</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="COP">{{number_format($descentrales*0.19, 2, ".", "")}}</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cbc:Percent>19.00</cbc:Percent>
                    <cac:TaxScheme>
                        <cbc:ID>01</cbc:ID>
                        <cbc:Name>IVA</cbc:Name>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
        </cac:TaxTotal>
        <cac:Item>
            <cbc:Description>CONSULTA CENTRALES</cbc:Description>
            <cac:StandardItemIdentification>
                <cbc:ID>CONSULTA CENTRALES</cbc:ID>
            </cac:StandardItemIdentification>
        </cac:Item>
        <cac:Price>
            <cbc:PriceAmount currencyID="COP">{{number_format($descentrales, 2, ".", "")}}</cbc:PriceAmount>
            <cbc:BaseQuantity unitCode="WSD">1.00</cbc:BaseQuantity>
        </cac:Price>
    </cac:CreditNoteLine>
    <cac:CreditNoteLine>
        <cbc:ID>3</cbc:ID>
        <cbc:CreditedQuantity unitCode="WSD">1.00</cbc:CreditedQuantity>
        <cbc:LineExtensionAmount currencyID="COP">{{number_format($dessoporte, 2, ".", "")}}</cbc:LineExtensionAmount>
        <cac:TaxTotal>
            <cbc:TaxAmount currencyID="COP">{{number_format($dessoporte*0.19, 2, ".", "")}}</cbc:TaxAmount>
            <cbc:TaxEvidenceIndicator>false</cbc:TaxEvidenceIndicator>
            <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="COP">{{number_format($dessoporte, 2, ".", "")}}</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="COP">{{number_format($dessoporte*0.19, 2, ".", "")}}</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cbc:Percent>19.00</cbc:Percent>
                    <cac:TaxScheme>
                        <cbc:ID>01</cbc:ID>
                        <cbc:Name>IVA</cbc:Name>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
        </cac:TaxTotal>
        <cac:Item>
            <cbc:Description>SOPORTE Y ASESORIA </cbc:Description>
            <cac:StandardItemIdentification>
                <cbc:ID>SOPORTE Y ASESORIA </cbc:ID>
            </cac:StandardItemIdentification>
        </cac:Item>
        <cac:Price>
            <cbc:PriceAmount currencyID="COP">{{number_format($dessoporte, 2, ".", "")}}</cbc:PriceAmount>
            <cbc:BaseQuantity unitCode="WSD">1.00</cbc:BaseQuantity>
        </cac:Price>
    </cac:CreditNoteLine>
    <cac:CreditNoteLine>
        <cbc:ID>4</cbc:ID>
        <cbc:CreditedQuantity unitCode="WSD">1.00</cbc:CreditedQuantity>
        <cbc:LineExtensionAmount currencyID="COP">{{number_format($desplataforma, 2, ".", "")}}</cbc:LineExtensionAmount>
        <cac:TaxTotal>
            <cbc:TaxAmount currencyID="COP">{{number_format($desplataforma*0.19, 2, ".", "")}}</cbc:TaxAmount>
            <cbc:TaxEvidenceIndicator>false</cbc:TaxEvidenceIndicator>
            <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="COP">{{number_format($desplataforma, 2, ".", "")}}</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="COP">{{number_format($desplataforma*0.19, 2, ".", "")}}</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cbc:Percent>19.00</cbc:Percent>
                    <cac:TaxScheme>
                        <cbc:ID>01</cbc:ID>
                        <cbc:Name>IVA</cbc:Name>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
        </cac:TaxTotal>
        <cac:Item>
            <cbc:Description>PROCESAMIENTO DE DATOS-PLATAFORMA</cbc:Description>
            <cac:StandardItemIdentification>
                <cbc:ID>PROCESAMIENTO DE DATOS-PLATAFORMA</cbc:ID>
            </cac:StandardItemIdentification>
        </cac:Item>
        <cac:Price>
            <cbc:PriceAmount currencyID="COP">{{number_format($desplataforma, 2, ".", "")}}</cbc:PriceAmount>
            <cbc:BaseQuantity unitCode="WSD">1.00</cbc:BaseQuantity>
        </cac:Price>
    </cac:CreditNoteLine>
    @if ($desprestamo > 0)
        <cac:CreditNoteLine>
            <cbc:ID>5</cbc:ID>
            <cbc:CreditedQuantity unitCode="WSD">1.00</cbc:CreditedQuantity>
            <cbc:LineExtensionAmount currencyID="COP">{{number_format($desprestamo, 2, ".", "")}}</cbc:LineExtensionAmount>
            <cac:TaxTotal>
                <cbc:TaxAmount currencyID="COP">0.00</cbc:TaxAmount>
                <cbc:TaxEvidenceIndicator>false</cbc:TaxEvidenceIndicator>
                <cac:TaxSubtotal>
                    <cbc:TaxableAmount currencyID="COP">{{number_format($desprestamo, 2, ".", "")}}</cbc:TaxableAmount>
                    <cbc:TaxAmount currencyID="COP">0.00</cbc:TaxAmount>
                    <cac:TaxCategory>
                        <cbc:Percent>0.00</cbc:Percent>
                        <cac:TaxScheme>
                            <cbc:ID>01</cbc:ID>
                            <cbc:Name>IVA</cbc:Name>
                        </cac:TaxScheme>
                    </cac:TaxCategory>
                </cac:TaxSubtotal>
            </cac:TaxTotal>
            <cac:Item>
                <cbc:Description>PRESTAMO A CORTO PLAZO</cbc:Description>
                <cac:StandardItemIdentification>
                    <cbc:ID>PRESTAMO A CORTO PLAZO</cbc:ID>
                </cac:StandardItemIdentification>
            </cac:Item>
            <cac:Price>
                <cbc:PriceAmount currencyID="COP">{{number_format($desprestamo, 2, ".", "")}}</cbc:PriceAmount>
                <cbc:BaseQuantity unitCode="WSD">1.00</cbc:BaseQuantity>
            </cac:Price>
        </cac:CreditNoteLine>
    @endif
</CreditNote>