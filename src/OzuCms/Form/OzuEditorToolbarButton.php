<?php

namespace Code16\OzuClient\OzuCms\Form;

enum OzuEditorToolbarButton: string
{
    case Bold = 'bold';
    case Italic = 'italic';
    case Highlight = 'highlight';
    case Link = 'link';
    case Separator = '|';
    case BulletList = 'bullet-list';
    case OrderedList = 'ordered-list';
    case Heading1 = 'heading-1';
    case Heading2 = 'heading-2';
    case Iframe = 'iframe';
    case Image = 'upload-image';
    case File = 'upload';
    case Video = 'video';
    case Quote = 'blockquote';
}
