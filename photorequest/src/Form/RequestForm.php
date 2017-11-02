<?php

namespace Drupal\photorequest\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ChangedCommand;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';



class RequestForm extends FormBase {

        public function getFormId() {
                return 'request_form';

        }

        public function buildForm(array $form, FormStateInterface $form_state) {

		$form['#prefix'] = '<div id="request-form-wrapper-id"><div class="form-group"><p class="request-form-error-message"></p>';
				
				$form['top_name'] = array(
				    '#type' => 'fieldset',
				    '#title' => t('Please provide the following information. Your content will be available for immediate download.'),
				  );
				
                $form['name'] = array(
                      '#type' => 'textfield',
                      '#title' => t('Name:*'),
                      '#required' => TRUE,
                      '#attributes' => [
							'class' => [
							    'form-control'
							]
						],
                );

                $form['organization'] = array(
                      '#type' => 'textfield',
                      '#title' => t('Organization:*'),
                      '#required' => TRUE,
                      '#attributes' => [
							'class' => [
							    'form-control'
							]
						],
                );

                $form['email'] = array(
                      '#type' => 'email',
                      '#title' => t('Email Address:*'),
                      '#required' => TRUE,
                      '#attributes' => [
							'class' => [
							    'form-control'
							]
						],
                );

                $form['why'] = array(
                      '#type' => 'textfield',
                      '#title' => t('Why do you want the photo:*'),
                      '#required' => TRUE,
                      '#attributes' => [
							'class' => [
							    'form-control'
							]
						],
                );

                $form['how'] = array(
                      '#type' => 'textfield',
                      '#title' => t('How will you use the photo:*'),
                      '#required' => TRUE,
                      '#attributes' => [
							'class' => [
							    'form-control'
							]
						],
                );
                
                $form['code'] = array(
                      '#type' => 'textfield',
                      '#title' => '',
                      '#required' => FALSE,
                      '#attributes' => [
							'class' => [
							    'form-control hidden code-text'
							]
						],
                );
                
                $form['site_name'] = array(
                      '#type' => 'textfield',
                      '#title' => '',
                      '#required' => FALSE,
                      '#attributes' => [
							'class' => [
							    'form-control hidden site-name-text'
							]
						],
                );

                $form['actions']['#type'] = 'actions';

                $form['actions']['submit'] = array(
                      '#type' => 'submit',
                      '#value' => $this->t('Submit'),
                      '#button_type' => 'primary',
		      '#attributes' => [
				'class' => [
				    'btn',
				    'btn-lg',
				    'btn-red',
				    'btn-primary',
				    'use-ajax-submit'
				]
		    ],
		    '#ajax' => [
			'wrapper' => 'my-form-wrapper-id',
			'callback' => 'Drupal\photorequest\Form\RequestForm::handleSubmit'
		    ],
		
                );
		/*$form['actions']['download'] = array(
                      '#type' => 'submit',
                      '#value' => $this->t('Download'),
                      '#button_type' => 'primary',
                      '#attributes' => [
                                'class' => [
                                    'btn',
                                    'btn-md',
                                    'btn-primary',
                                    'use-ajax-submit'
                                ],
				'style' => ['display:none']	
                    ],
                    '#ajax' => [
                        'wrapper' => 'my-form-wrapper-id',
                        'callback' => 'Drupal\photorequest\Form\RequestForm::handleDownload'
                    ],

                );*/
                
                /*$form['actions']['download'] = array(
                      '#type' => 'button',
                      '#value' => $this->t('Download Image'),
                      '#button_type' => 'primary',
                      '#attributes' => [
                                'class' => [
                                    'btn',
                                    'btn-big',
                                    'btn-download',
                                    'btn-red',
                                    'hidden-download-button'
                                ],
    'style' => [ 'text-align: center; margin: 0 auto; width: 70%; display:none'] 
                    ],
                    '#ajax' => [
                        'wrapper' => 'my-form-wrapper-id',
                        'callback' => 'Drupal\photorequest\Form\RequestForm::handleDownload'
                    ],

                );*/


		$form['#suffix'] = '</div></div>';

                return $form;
        }
        
        

	public function handleSubmit(array &$form, FormStateInterface $form_state) {
		
		$mail = new PHPMailer;

		/*$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = 'remote.easternstate.org';  // Specify main and backup SMTP servers
		$mail->SMTPAuth = false;                               // Enable SMTP authentication
		$mail->Username = '';                 // SMTP username
		$mail->Password = '';                            // Enable encryption, 'ssl' also accepted*/
		
		$mail->setFrom('no-reply@easternstate.org');
		//$mail->addAddress('jeff.majek@gmail.com', 'My Friend');
		
		$mail->WordWrap = 50;                                 // Set word wrap to 50 characters
		$mail->isHTML(true);                                  // Set email format to HTML
		
		
		$ajax_response = new AjaxResponse();
		
		if($form_state->hasAnyErrors())
		{ 
			$error_message = "<span style='color:#DB322B;'>Please ensure all fields are complete and that your email address is correct.</span>";
			$ajax_response->addCommand(new HtmlCommand('#request-form-wrapper-id .request-form-error-message', $error_message));
			//$errors = drupal_get_messages('error');
		} else {
			
			$msg = "A new file was downloaded from " . $form_state->getValue('site_name') .
					"<br> – Image: " . $form_state->getValue('code') .
					"<br> – Name: " . $form_state->getValue('name') .
					"<br> – Organization: " . $form_state->getValue('organization') .
					"<br> – Email: " . $form_state->getValue('email') .
					"<br> – Why do you want the photo: " . $form_state->getValue('why') .
					"<br> – How will you use the photo: " . $form_state->getValue('how') .
					"<br> – Submitted on: " . date("F j, Y, g:i a");
					
			//$msg = "Testing press@esp email";
			
			/*$to      = 'jeff@interactivemechanics.com';
			$subject = 'Press File downloaded from ' . $form_state->getValue('site_name');
			$message = $msg;
			$headers = 'From: jeff@interactivemechanics.com' . "\r\n" .
			    'Reply-To: jeff@interactivemechanics.com' . "\r\n" .
			    'X-Mailer: PHP/' . phpversion();
			
			mail($to, $subject, $message, $headers);*/
			
			//$to = "press@easternstate.org";
			$to = "press@easternstate.org";
			$subject = 'Press File downloaded from ' . $form_state->getValue('site_name');
		
		    // Always set content-type when sending HTML email
		    $headers = "MIME-Version: 1.0" . "\r\n";
		    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		    $headers .= 'From: no-reply@easternstate.org' . "\r\n";
		    
		    //mail($to, $subject, $msg, $headers);
		    
		    
			$mail->addAddress($to, $form_state->getValue('name'));
			$mail->addAddress('jeff.majek@gmail.com', $form_state->getValue('name'));
		    $mail->Subject = $subject;
			$mail->Body    = $msg;
			$mail->AltBody = $subject;
	
			$mail->send();
			
			
			$ajax_response->addCommand(new InvokeCommand('#request-form-wrapper-id form', 
			'css', array('display', "none")));
			
			/*$ajax_response->addCommand(new InvokeCommand('#request-form-wrapper-id form input', 
			'css', array('display', "none")));
			$ajax_response->addCommand(new InvokeCommand('#request-form-wrapper-id form label', 
			'css', array('display', "none")));
			$ajax_response->addCommand(new InvokeCommand('#request-form-wrapper-id form input#edit-download', 
			'css', array('display', "block")));
    
			$ajax_response->addCommand(new InvokeCommand('#request-form-wrapper-id form input.hidden-download-button', 'css', array('display', "block")));*/
   
   			$ajax_response->addCommand(new InvokeCommand('.btn-download', 'css', array('display', "block")));
	        // Return the AjaxResponse Object.
	        
	    }
	    
	    return $ajax_response;
	}

	public function handleDownload(array &$form, FormStateInterface $form_state) {
		
	}


    public function validateForm(array &$form, FormStateInterface $form_state) {

    }

	public function ajaxRebuildForm(array &$form, FormStateInterface $form_state) {
	    return $form;
	}

    public function submitForm(array &$form, FormStateInterface $form_state) {
        /*
            foreach ($form_state->getValues() as $key => $value) {
              drupal_set_message($key . ': ' . $value);
            }
        */
    }

}
