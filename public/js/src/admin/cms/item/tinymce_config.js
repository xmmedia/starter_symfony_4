export default {
    plugins: [
        'advlist autolink lists link image charmap hr searchreplace help',
        'visualblocks media table powerpaste imagetools moxiemanager',
    ],

    menu: {
        edit  : {
            title: 'Edit'  ,
            items: 'undo redo | cut copy paste pastetext | selectall | searchreplace'
        },
        insert: {
            title: 'Insert',
            items: 'media image link | hr nonbreaking'
        },
        view  : {
            title: 'View'  ,
            items: 'visualchars visualblocks visualaid'
        },
        format: {
            title: 'Format',
            items: 'bold italic underline | removeformat'
        },
        table : {
            title: 'Table' ,
            items: 'inserttable tableprops deletetable | cell row column'
        },
    },

    toolbar1: 'bold italic | alignleft aligncenter alignright | formatselect | fontsizeselect',
    toolbar2: 'table | bullist numlist | link unlink image media | charmap visualblocks | removeformat | help',

    statusbar: false,
    branding: false,
    relative_urls: false,
    browser_spellcheck: true,

    image_advtab: false,
    image_caption: false,
    image_class_list: [
        { title: 'None', value: '' },
        { title: 'Left Align', value: 'content-img-left' },
        { title: 'Right Align', value: 'content-img-right' },
        { title: 'Center Align', value: 'content-img-center' },
    ],

    // disable the link targets
    target_list: false,

    body_class: 'tinymce-content px-4',

    block_formats: 'Paragraph=p;Header 1=h1;Header 2=h2;Header 3=h3;Header 4=h4',
    fontsize_formats: '12px 14px 16px 18px 20px',

    // this is duplicated in app/config.yml
    valid_elements: [
        'a[href|target=_blank|class|rel]',
        'p[style|class|align]',
        'div[style|class]',
        'span[style|class]',
        'ul[style|class],ol[style|class],li[style|class]',
        'br,h1,h2,h3,h4,h5',
        'strong/b,em/i',
        'table[class],tr,td[style|class],th[style|class],tbody,thead',
        'img[src|alt|width|height|class|style]',
        'iframe[src|width|height|frameborder]',
        'hr',
    ].join(','),

    // media plugin config
    // @todo determine if we can block uploading video files but allow embedding (and check other options)
    media_poster: false,
    media_dimensions: false,
    media_alt_source: false,

    height: 500,
}
