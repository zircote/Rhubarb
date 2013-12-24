# -*- coding: utf-8 -*-

import sys, os

extensions = []

templates_path = ['_templates']

master_doc = 'index'

project = u'Rhubarb'
copyright = u'2012, Robert Allen'

version = '2013-05-09'

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
pygments_style = 'sphinx'
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
     u'Robert Allen', 'Rhubarb', 'One line description of project.',
     'Miscellaneous'),
]
