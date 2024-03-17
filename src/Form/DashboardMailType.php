<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Form;

use Spyck\VisualizationBundle\Message\MailMessage;
use Spyck\VisualizationBundle\Service\ViewService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;

final class DashboardMailType extends AbstractType
{
    public function __construct(private readonly ViewService $viewService)
    {
    }

    /**
     * Build the form.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('description', TextType::class)
            ->add('variables', CollectionType::class, [
                'compound' => true,
                'allow_extra_fields' => true,
            ])
            ->add('view', TextType::class, [
                'constraints' => [
                    new Choice(choices: $this->getViews()),
                    new NotBlank(),
                ],
            ])
            ->add('merge', CheckboxType::class);
    }

    /**
     * Configure options for this form.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => MailMessage::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }

    private function getViews(): array
    {
        $data = $this->viewService->getViews();

        return array_keys($data);
    }
}
