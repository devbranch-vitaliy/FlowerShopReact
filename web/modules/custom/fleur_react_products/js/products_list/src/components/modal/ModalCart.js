import React from 'react';
import Modal from 'react-modal';
import {dispatch, useGlobalState} from "../../utilits/globals";
import ModalColors from "./ModalColors";

const ModalCart = () => {
  const [modal_cart] = useGlobalState('modal_cart');

  // Customise styles
  if (modal_cart.show) {
    document.body.classList.add('modal-open');
  }
  else {
    document.body.classList.remove('modal-open');
  }
  Modal.defaultStyles.overlay.backgroundColor = 'rgba(0,0,0,0.5)';
  const customStyles = {
    content: {
      top: '50%',
      left: '50%',
      right: 'auto',
      bottom: 'auto',
      marginRight: '-50%',
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

        {modal_cart.product && modal_cart.choice.color &&
          <ModalColors colors={modal_cart.product.colors} default_color={modal_cart.choice.color}/>
        }

      </div>
    </Modal>
  )
};

export default ModalCart;
