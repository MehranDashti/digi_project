<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Variant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateVariantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('color', TextType::class, ['required' => true])
            ->add('price', NumberType::class, ['required' => true])
            ->add("product", EntityType::class, [
                "class" => Product::class,
                "choice_label" => "title",
                "label" => "product",
                "required" => true,
                "translation_domain" => "product",
                'disabled' => true
            ])
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Variant::class,
        ]);
    }
}
