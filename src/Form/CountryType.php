<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\Country;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CountryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('uuid', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 36]),
                ],
            ])
            ->add('name', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 255]),
                ],
            ])
            ->add('region', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => 100]),
                ],
            ])
            ->add('subRegion', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => 100]),
                ],
            ])
            ->add('demonym', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => 100]),
                ],
            ])
            ->add('population', IntegerType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\PositiveOrZero(),
                ],
            ])
            ->add('independent', CheckboxType::class, [
                'required' => false,
            ])
            ->add('flag', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => 500]),
                    new Assert\Url(),
                ],
            ])
            ->add('currency', CurrencyType::class, [
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Country::class,
            'csrf_protection' => false, // Disable CSRF for API
        ]);
    }
}
