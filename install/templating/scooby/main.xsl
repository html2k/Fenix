<xsl:template match="/page">
    <html>
        <head>
            <xsl:apply-templates select="head"/>
        </head>
        <body>
            <xsl:apply-templates select="body"/>
        </body>
    </html>
</xsl:template>


<xsl:template match="head">
    <title>Fenix</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
</xsl:template>

<xsl:template match="body">
    
</xsl:template>
