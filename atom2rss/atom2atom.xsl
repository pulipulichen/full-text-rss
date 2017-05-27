<?xml version="1.0" encoding="UTF-8" ?>
<x:stylesheet version="1.0" 
	exclude-result-prefixes="atom xhtml dc"
	xmlns:x="http://www.w3.org/1999/XSL/Transform" 
	xmlns="http://www.w3.org/2005/Atom"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:xhtml="http://www.w3.org/1999/xhtml"
	xmlns:atom="http://purl.org/atom/ns#">
	<x:output encoding="UTF-8" indent="yes" method="xml" />

<x:template match="dc:subject">
	<category term="{.}"/>
</x:template>

<x:template match="atom:tagline">
	<subtitle><x:apply-templates/></subtitle>
</x:template>

<x:template match="atom:modified">
	<updated><x:apply-templates/></updated>
</x:template>

<x:template match="atom:copyright">
	<rights><x:apply-templates/></rights>
</x:template>

<x:template match="atom:issued">
	<published><x:apply-templates/></published>
</x:template>

<x:template match="atom:url">
	<uri><x:apply-templates/></uri>
</x:template>

<x:template match="@url">
	<x:attribute name="uri"><x:value-of select="."/></x:attribute>
</x:template>
	
<x:template match="atom:info|atom:created|atom:feed/@version|atom:*/@mode"/>	

<x:template match="atom:*[not(local-name() = 'link')]/@type[.='text/html']">
	<x:attribute name="type">html</x:attribute>
</x:template>

<x:template match="atom:*[not(local-name() = 'link')]/@type[.='application/xhtml+xml']">
	<x:attribute name="type">xhtml</x:attribute>
</x:template>

<x:template match='atom:*'>
	<x:element name="{local-name()}" namespace="http://www.w3.org/2005/Atom">
	  <x:apply-templates select="@*|node()"/>
	</x:element>
</x:template>

<x:template match="@*|node()">
	<x:copy>
		<x:apply-templates select="@*|node()"/>
	</x:copy>
</x:template>
</x:stylesheet>
