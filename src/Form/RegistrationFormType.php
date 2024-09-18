<?php
/**
 * RegistrationFormType class.
 *
 * This form handles user registration, including terms agreement and password validation.
 */

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class RegistrationFormType.
 *
 * Builds the form for user registration, including email, password, and terms agreement.
 */
class RegistrationFormType extends AbstractType
{
    /**
     * Build the registration form.
     *
     * @param FormBuilderInterface $builder Form builder instance
     * @param array                $options Additional options for the form
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, ['label' => 'label.email',
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'label.agree_terms',
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'user.agree_terms',
                    ]),
                ],
            ])
            ->add('password', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'label' => 'label.password',
                'mapped' => true,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'user.password.not_blank',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'user.password.min_length',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
        ;
    }

    /**
     * Configure default options for the form.
     *
     * @param OptionsResolver $resolver Options resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
