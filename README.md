# Counter
Creates a Counter compliant report from Clicky data. Basically, this means there is an XML POST request sent to our
endpoint (which will be setup at jomi.com/usage/counter) that contains information on the customer, the organization
within which the customer is operating,
and the dates between which the customer wants data. We must in turn respond with monthly statistics for usage of the
JoMI site at their institution between the dates provided.

The python folder represents an abandoned work in progress, if we ever need to come back to it I suggest we refactor to be more similar to the php folder. The php folder represents a complete implementation of the COUNTER protocol, along with a front-end visulization of the response.

General information on the SUSI protocols can be found [here](http://www.niso.org/schemas/sushi/), although it is 
not told in the clearest of manners.

Counter Reports are generated aanytime someone makes a SUSHI request for usage data. 
The request is a POST request to our server with an XML file. That XML file should behave in accordance 
with the ReportRequest defined in [this schema](http://www.niso.org/schemas/sushi/sushi1_7.xsd), 
a diagram of which can be found [here](http://www.niso.org/schemas/sushi/diagrams/sushi1_7_ReportRequest.png),
and a sample (which Nolan constructed with the schema and is hopefully correct) request is in the [sampleRequest.xml](https://github.com/jomijournal/Counter/blob/master/tests/sampleRequest.xml) file in this repository.


For the request itself, we need to parse the  begin date and end date, stored in the 'Begin' and 'End'
children of the object whose XPath is
'ReportDefinition/Filters/UsageDateRange' from the root. The customer data we also need, as that is how we filter by
institution. We extract the customer ID from the field with the XPath "Requestor/ID" and use that ID to query our
database which contins all of the ip blocks controlled by each institution.

Counter Reports contain usage data on a month by month basis, and SUSHI requests give a general date range, 
so from that date range it is necessary to extract a list of months. Then, you can take the list of months and check 
if cached data for that months exist. If it doesn't exist, get the data from analytics; in our case, for now, Clicky,
although that is not the greatest service. The query is something along the lines of '''type=segmentation&segments=visitors,actions&ip_address=851290368&date=2015-04-01,2015-04-07''', with the relevant ips and dates put in. **Note: Clicky ony allows you to query 7 days at a time.

Then just assemble the data and return it. This returned value must obey [these](http://www.niso.org/schemas/sushi/counter4_1.xsd) [xsd files](http://www.niso.org/schemas/sushi/counter4_1.xsd) and kind of [this diagram](http://www.niso.org/schemas/sushi/diagrams/sushi1_7_ReportResponse.png), and what I've been returning looks like [this](https://github.com/jomijournal/Counter/blob/master/tests/sampleResponse.xml)


# Testing

For Python:

On unix: Open 2 Terminals. One will be the client (and show the response) and one will be the server (and show the internal server errors).
 * To start the server, cd into this folder and do 'python indexing.py'
 * To start the client, cd into this folder and do 'curl -X POST -d @sampleRequest.xml http://localhost:8888/'
   * Substitute sampleRequest.xml for other requests if wanted


For PHP:

Just upload the files to a server running WP. It was necessary to hack around in the JoMI theme to make certain that
the XML returned wasn't embelished and put in a more bloggy format. Also make certain all of the require paths are
satisfied. Also, usage.php, the front-end display, requires [Datepickr](https://github.com/joshsalverda/datepickr) by
Josh Salverda.
