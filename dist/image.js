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
      TextControl = _wp$components.TextControl,
      TextareaControl = _wp$components.TextareaControl,
      CheckboxControl = _wp$components.CheckboxControl;
  var InspectorControls = wp.editor.InspectorControls;
  var el = createElement;

  function isValidBlock(block) {
    return block.name == 'core/image';
  } // Update the core/image block to include Advanced Pins attributes.


  function registerBlock(settings, name) {
    if (!isValidBlock({
      name: name
    }) || !settings) return settings;
    var coreAttributes = {
      advancedDescription: {
        type: 'string',
        source: 'attribute',
        attribute: 'data-pin-description',
        selector: 'img',
        "default": ''
      },
      hoverOptOut: {
        type: 'boolean',
        source: 'attribute',
        attribute: 'data-pin-no-hover'
      },
      isHiddenImage: {
        type: 'boolean',
        source: 'attribute',
        attribute: 'data-hidden-pin'
      },
      pinID: {
        type: 'string',
        source: 'attribute',
        attribute: 'data-pin-id'
      }
    };
    var missingAttributesList = Object.keys(coreAttributes).filter(function (attribute) {
      return !(attribute in settings.attributes);
    }); // Create a lsit of attributes missing from `settings`.

    if (missingAttributesList.length > 0) {
      var updatedAttributes = missingAttributesList.reduce(function (updatedAttributes, attr) {
        return Object.assign({}, updatedAttributes, _defineProperty({}, attr, coreAttributes[attr]));
      }, {});
      var newSettings = Object.assign({}, settings);
      newSettings.attributes = Object.assign(settings.attributes, updatedAttributes);
      return newSettings;
    }

    return settings;
  }
  /* TODO: When the constant 'SCRIPT_DEBUG' is `true`, this function throws React errors. */
  // Corrects the data-attributes in Editor so it is stored properly for next pageload.


  function getSaveElement(element, block, attributes) {
    if (!isValidBlock(block)) return element;

    var _element = cloneElement(element);

    var refImg = findChildImageComponent(_element); // Find the reference to <img> object.

    if (attributes.advancedDescription) Object.assign(refImg.props, {
      'data-pin-description': attributes.advancedDescription
    });
    if (attributes.hoverOptOut) refImg.props['data-pin-no-hover'] = true;
    if (attributes.isHiddenImage) refImg.props['data-hidden-pin'] = true;
    if (attributes.pinID) refImg.props['data-pin-id'] = attributes.pinID;
    return el('figure', _element.props);
  } // Inserts textarea and checkbox into the gutenberg sidebar for Pin controls.


  function createInspector(props) {
    var pinterestDescription =
    /* The Pinterest Description in the Block sidebar. */
    {
      label: 'Pinterest Description',
      value: props.attributes.advancedDescription,
      onChange: function onChange(advancedDescription) {
        return props.setAttributes({
          advancedDescription: advancedDescription
        });
      } // Does not compact. 

    };
    var pinID =
    /* Optional re-pin ID for existing Pins. */
    {
      label: 'Pin ID',
      help: 'Use this to redirect Pins of this image to an existing Pin.',
      value: props.attributes.pinID,
      onChange: function onChange(url) {
        return props.setAttributes({
          pinID: url
        });
      }
    };
    var hoverOptOut =
    /* Prevents the hover save button from showing up on this image. */
    {
      heading: 'Hover Opt Out',
      checked: props.attributes.hoverOptOut,
      onChange: function onChange(checked) {
        return props.setAttributes({
          hoverOptOut: checked
        });
      }
    };
    var isHiddenImage =
    /* Enables this image to be hidden on the page. */
    {
      heading: 'Make this a hidden Pinterest image.',
      checked: props.attributes.isHiddenImage,
      onChange: function onChange(checked) {
        return props.setAttributes({
          isHiddenImage: checked
        });
      }
    };
    return el(InspectorControls, null, el(PanelBody, {
      title: "Advanced Pins"
    }, el(TextareaControl, pinterestDescription), el(TextControl, pinID), el(CheckboxControl, hoverOptOut), el(CheckboxControl, isHiddenImage)));
  } // Reads the stored values and updates attributes before parsing blocks.
  // Required to prevent block invalidation errors. 


  function updateAttributes(attributes, block, rawHTML, metadata) {
    if (!isValidBlock(block)) return attributes;
    /* Would prefer a better way to get child than manually reading 
     * nested HTML string attributes. */

    var tree = jQuery(rawHTML);
    var img = tree.find('img');
    var advancedDescription = img.data('pinDescription');
    var hoverOptOut = img.data('pinNoHover');
    var isHiddenImage = img.data('hiddenPin');
    var pinID = img.data('pinID');
    return Object.assign(attributes, {
      advancedDescription: advancedDescription,
      hoverOptOut: hoverOptOut,
      isHiddenImage: isHiddenImage,
      pinID: pinID
    });
  }
  /* Immediate implementation for "getting" the <EditImage /> WPReact component. */


  function findChildImageComponent(figure) {
    // if (!Array.isArray(figure.props.children))
    //   return figure.props.children
    // else
    //   return figure.props.children[0]
    if (!figure.props.children) {
      return figure;
    }

    if (figure.props.children) if (!figure.props.children.props || !figure.props.children.props.children) {
      console.log('Expected to find grandchildren for component ');
      console.log("Lowest layer of children: ", figure.props.children);
      return figure;
    }
    return figure.props.children.props.children[0];
  } // Currently modeled after example on WP docs; need to understand this better. 


  var imageUpdateEdit = createHigherOrderComponent(function (BlockEdit) {
    return function (props) {
      if (!isValidBlock(props)) return BlockEdit(props);
      return el(Fragment, null, el(BlockEdit, props), createInspector(props));
    };
  }, 'imageEditorUpdatedControls');
  wp.hooks.addFilter('blocks.registerBlockType', 'apx-update-block-core/image', registerBlock);
  wp.hooks.addFilter('blocks.getBlockAttributes', 'apx-update-attributes-core/image', updateAttributes);
  wp.hooks.addFilter('blocks.getSaveElement', 'apx-update-save-core/image', getSaveElement);
  wp.hooks.addFilter('editor.BlockEdit', 'apx-update-edit-core/image', imageUpdateEdit);
})();