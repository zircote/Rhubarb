# -*- coding: utf-8 -*-

extensions = []

templates_path = ['_templates']

master_doc = 'index'

project = u'Rhubarb'
copyright = u'2012-2014, Robert Allen'

version = '3.2-dev'

release = ''

exclude_patterns = ['_build']

pygments_style = 'sphinx'

html_theme = 'pyramid'

html_static_path = ['_static']

htmlhelp_basename = 'Rhubarbdoc'
from sphinx.highlighting import lexers
from pygments.lexers.web import PhpLexer

lexers['php'] = PhpLexer(startinline=True)
lexers['php-annotations'] = PhpLexer(startinline=True)
primary_domain = "php"

latex_elements = {
}

latex_documents = [
    ('index', 'Rhubarb.tex', u'Rhubarb Documentation',
     u'Robert Allen', 'manual'),
]

man_pages = [
    ('index', 'swagger-php', u'Rhubarb Documentation',
     [u'Robert Allen'], 1)
]

texinfo_documents = [
    ('index', 'Rhubarb', u'Rhubarb Documentation',
     u'Robert Allen', 'Rhubarb', 'A PHP library connecting to Celery',
     'Miscellaneous'),
]
