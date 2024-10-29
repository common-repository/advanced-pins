"use strict";

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

(function () {
  var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
  var _wp$element = wp.element,
      Fragment = _wp$element.Fragment,
      cloneElement = _wp$element.cloneElement,
      createElement = _wp$element.createElement;
  var _wp$components = wp.components,
      PanelBody = _wp$components.PanelBody,
      TextareaControl = _wp$components.TextareaControl,
      CheckboxControl = _wp$components.CheckboxControl;
  var InspectorControls = wp.editor.InspectorControls;
  var el = createElement;

  function getRootBlockType() {
    return 'ul';
  }

  function isValidBlock(block) {
    return block.name == 'core/gallery';
  }

  function registerBlock(settings, name) {
    if (!isValidBlock({
      name: name
    }) || !settings) return settings;
    var coreAttributes = {
      galleryDescription: {
        type: 'string',
        source: 'attribute',
        attribute: 'data-gallery-description',
        "default": ''
      },
      isHiddenGallery: {
        type: 'boolean',
        source: 'attribute',
        "default": false,
        attribute: 'data-hidden-gallery'
      }
    };
    var missingAttributes = Object.keys(coreAttributes).filter(function (attribute) {
      return !(attribute in settings.attributes);
    });

    if (missingAttributes.length) {
      var updatedAttributes = missingAttributes.reduce(function (updatedAttributes, attr) {
        return Object.assign({}, updatedAttributes, _defineProperty({}, attr, coreAttributes[attr]));
      }, {});
      var newSettings = Object.assign({}, settings);
      newSettings.attributes = Object.assign(settings.attributes, updatedAttributes);
      return newSettings;
    }

    return settings;
  } // Corrects the data-attributes in Editor so it is stored properly for next pageload.


  function getSaveElement(element, block, attributes) {
    if (!isValidBlock(block)) return element;
    if (attributes.galleryDescription) element.props['data-gallery-description'] = attributes.galleryDescription;
    if (attributes.isHiddenGallery) element.props['data-hidden-gallery'] = true;
    return element;
  } // Reads the stored values and updates attributes before parsing blocks.
  // Required to prevent block invalidation errors. 


  function updateAttributes(attributes, block, rawHTML, metadata) {
    if (!isValidBlock(block)) return attributes; // Would prefer a better way to get data than manually reading HTML string attributes.

    var tree = jQuery(rawHTML);
    var galleryDescription = tree.data('galleryDescription') || attributes.galleryDescription;
    var isHiddenGallery = tree.data('hiddenGallery') || attributes.isHiddenGallery;
    return Object.assign(attributes, {
      galleryDescription: galleryDescription,
      isHiddenGallery: isHiddenGallery
    });
  } // Inserts textarea and checkbox into the gutenberg sidebar for Pin controls.


  function createInspector(props) {
    if (!isValidBlock(props)) return props;
    var textarea =
    /* The Pinterest Description in the Block sidebar. */
    {
      label: 'Gallery Description',
      help: 'Apply this description to each image in the gallery.',
      value: props.attributes.galleryDescription || '',
      onChange: function onChange(galleryDescription) {
        return props.setAttributes({
          galleryDescription: galleryDescription
        });
      } // Does not compact. 

    };
    var checkbox =
    /* The Hover Pin Opt Out checkbox. */
    {
      heading: 'Make this a hidden Pin gallery',
      checked: props.attributes.isHiddenGallery,
      onChange: function onChange(checked) {
        return props.setAttributes({
          isHiddenGallery: checked
        });
      }
    };
    return el(InspectorControls, null, el(PanelBody, {
      title: "Advanced Pins"
    }, el(TextareaControl, textarea), el(CheckboxControl, checkbox)));
  } // Designed to re-implement the default WordPress save for image blocks.


  function save(props) {
    return el(getRootBlockType(), props);
  } // Currently modeled after example on WP docs; need to understand this better. 


  var galleryUpdateEdit = createHigherOrderComponent(function (BlockEdit) {
    return function (props) {
      if (!isValidBlock(props)) return BlockEdit(props);
      return el(Fragment, null, el(BlockEdit, props), createInspector(props));
    };
  }, 'galleryUpdateEdit');
  wp.hooks.addFilter('blocks.registerBlockType', 'apx-update-block-core/gallery', registerBlock);
  wp.hooks.addFilter('blocks.getBlockAttributes', 'apx-update-attributes-core/gallery', updateAttributes);
  wp.hooks.addFilter('blocks.getSaveElement', 'apx-update-save-core/gallery', getSaveElement);
  wp.hooks.addFilter('editor.BlockEdit', 'apx-update-edit-core/gallery', galleryUpdateEdit);
})();