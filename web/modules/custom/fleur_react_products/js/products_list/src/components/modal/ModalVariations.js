import React from 'react';
import {dispatch} from "../../utilits/globals";
import {getRelationshipEntity} from "../../utilits/api";

const ModalVariations = ({ variations, default_variation }) => {
  const variationNames = [];
  return (
    <div className={'product-variations-wrapper'}>
      <label className="control-label">Select color:</label>
      <div className={'product-variations'}>
        {variations && default_variation && variations.map(variation => {
          const variation_data = getRelationshipEntity(variation);
          let addVariation = false;

          // Check if such size already exists.
          if (variationNames.find(el => el === variation_data.title) === undefined) {
            variationNames.push(variation_data.title);
            addVariation = true;
          }

          return (variation_data && addVariation &&
            <button
              className={`variation-button ${variation.id === default_variation.id ? 'active' : ''}`}
              value={variation.id}
              key={variation.id}
              onClick={() => dispatch({type: 'chooseVariation', variation: variation})}
            >{`${variation_data.title} (${variation_data.price.formatted})`}</button>
          )
        })}
      </div>
    </div>
  )
};

export default ModalVariations;
