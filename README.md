# Counter
Creates a Counter compliant report from Clicky data

General information on the SUSI protocols can be found [here](http://www.niso.org/schemas/sushi/), although it is 
not told in the clearest of manners.

Counter Reports are generated nytime someone makes a SUSHI request for usage data. 
The request is a POST request to our server with an XML file. That XML file should behave in accordance 
with the ReportRequest defined in [this schema](http://www.niso.org/schemas/sushi/sushi1_7.xsd), 
a diagram of which can be found [here](http://www.niso.org/schemas/sushi/diagrams/sushi1_7_ReportRequest.png),
and a sample (which Nolan constructed with the schema and is hopefully correct) request is in the [sampleRequest.xml](https://github.com/jomijournal/Counter/blob/master/sampleRequest.xml) file in this repository.
Basically, the request contains information on the customer, the organization within which the customer is operating,
and the dates between which the customer wants data. 

For the request itself, we presumably only need to parse the 
begin date and end date, stored in the 'Begin' and 'End' children of the object whose XPath is
'ReportDefinition/Filters/UsageDateRange' from the root. The customer data we could also parse and log, or perhaps only limit those to whom we give information, but for a basic setup we can safely ignore.

Counter Reports contain usage data on a month by month basis, and SUSHI requests give a general date range, 
so from that date range it is necessary to extract a list of months. Then, you can take the list of months and check if cached data for that months exist. If it doesn't exist, get the data from analytics.

Then just assemble it and return it. This part I will describe better later.


# Testing

On unix: Open 2 Terminals. One will be the client (and show the response) and one will be the server (and show the internal server errors).
 * To start the server, cd into this folder and do 'python indexing.py'
 * To start the client, cd into this folder and do 'curl -X POST -d @sampleRequest.xml http://localhost:8888/'
   * Substitute sampleRequest.xml for other requests if wanted
