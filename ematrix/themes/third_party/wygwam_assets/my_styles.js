/*CKEDITOR.stylesSet.add( 'my_styles',
[
    // Block Styles
    { name: 'Blue Title', element: 'h2', styles: { 'color': 'Blue' } },
    { name: 'Red Title' , element: 'h3', styles: { 'color': 'Red' } },

    // Inline Styles
    { name: 'CSS Style', element: 'span', attributes: { 'class': 'my_style' } },
    { name: 'Marker: Yellow', element: 'span', styles: { 'background-color': 'Yellow' } }
]);*/

CKEDITOR.addStylesSet( 'my_styles',
[
     // Block Styles Ð address, div, h1, h2, h3, h4, h5, h6, p and pre
     { name : '1st-head', element : 'h3', styles: { 'font-size': '18px' } },
     { name : '2nd-head', element : 'h4', styles: { 'font-size': '16px' } },
     { name : '3rd-head', element : 'h5', styles: { 'font-size': '14px' } },
     { name : '3rd-head-italic', element : 'h5', styles: { 'font-size': '14px', 'font-style': 'italic' } },

     // Object Styles Ð a, embed, hr, img, li, object, ol, table, td, tr and ul
     { name : 'Horizontal Line', element : 'hr', styles: { 'border': '1px solid #ccc' } },

     // Inline Styles Ð spans and special classes and ids
     { name : 'Highlight Yellow', element : 'span', styles: { 'background-color': 'Yellow' }  },
     { name : 'Large Text', element : 'span', attributes : { 'class' : 'large' } },
     { name : 'Small Text', element : 'span', attributes : { 'class' : 'small' } }
]);