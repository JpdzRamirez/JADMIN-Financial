<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:wcf="http://wcf.dian.colombia">
   <soap:Header/>
   <soap:Body>
      <wcf:SendBillSync>
         <wcf:fileName>{{$numfact}}</wcf:fileName>
         <wcf:contentFile>{{$contenido}}</wcf:contentFile>
         </wcf:SendBillSync>
   </soap:Body>
</soap:Envelope>