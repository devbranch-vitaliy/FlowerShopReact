import React from 'react';
import {dispatch} from "../../utilits/globals";

const ModalColors = ({ colors, default_color }) => (
  <div className={'product-colors-wrapper'}>
    <label className="control-label">Select color:</label>
    <div className={'product-colors'}>
      {colors && default_color && colors.map(color => (
        <button
          className={`color-button ${color === default_color ? 'active' : ''}`}
          value={color}
          key={color}
          style={{
            backgroundColor: color
          }}
          onClick={() => dispatch({type: 'chooseColor', color: color})}
        />
      ))}
    </div>
  </div>
);

export default ModalColors;
