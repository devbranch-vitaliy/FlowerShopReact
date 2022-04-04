import React from 'react';
import Modal from 'react-modal';
import {dispatch, useGlobalState} from "../../utilits/globals";
import ModalColors from "./ModalColors";
import ModalVariations from "./ModalVariations";
import {getRelationshipEntity} from "../../utilits/api";
import ModalSubmit from "./ModalSubmit";

const ModalCart = () => {
  const [modal_cart] = useGlobalState('modal_cart');

  // Customise styles
  if (modal_cart.show) {
    document.body.classList.add('modal-open');
  }
  else {
    document.body.classList.remove('modal-open');
  }
  // Modal.defaultStyles.overlay.backgroundColor = 'rgba(0,0,0,0.5)';
  // Modal.defaultStyles.overlay.zIndex = 999;
  const customStyles = {
    overlay: {
      backgroundColor: 'rgba(0,0,0,0.5)',
      zIndex: 999
    },
    content: {
      top: '50%',
      left: '50%',
      right: 'auto',
      bottom: 'auto',
      marginRight: '-50%',
      padding: 0,
      transform: 'translate(-50%, -50%)',
    },
  };

  function closeModal() {
    dispatch({type: 'hideCart'});
  }

  return (
    <Modal
      isOpen={modal_cart.show}
      onRequestClose={closeModal}
      style={customStyles}
      ariaHideApp={false}
    >
      {modal_cart.product && modal_cart.choice &&
        <div className={'product-modal-cart'}>
          <div className={'product-modal-header'}>
            {modal_cart.product &&
              <h2>{modal_cart.product.title}</h2>
            }
            <button
              className={'close-modal'}
              aria-label={'Close'}
              onClick={closeModal}
            />
          </div>

          <div className={'product-modal-body'}>
            <ModalColors colors={modal_cart.product.colors} default_color={modal_cart.choice.color}/>
            <ModalVariations variations={modal_cart.product.variations} default_variation={modal_cart.choice.variation} />
          </div>

          <div className={'modal-footer'}>
            <div className={'product-current-price'}>{getRelationshipEntity(modal_cart.choice.variation).price.formatted}</div>
            <ModalSubmit />
          </div>

        </div>
      }
    </Modal>
  )
};

export default ModalCart;
